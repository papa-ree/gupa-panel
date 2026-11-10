<?php

namespace Bale\GupaPanel\Services;

use Bale\Cms\Models\BaleList;
use Bale\GupaPanel\Jobs\SyncLogsFromTenant;
use Bale\GupaPanel\Jobs\SyncMasterDataToTenant;
use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Models\PanelRequestLog;
use Bale\GupaPanel\Models\PanelWhitelist;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GupaSyncService
{
    public function syncAll(): void
    {
        if (! config('gupa-panel.enabled', true)) {
            return;
        }
        $tenants = BaleList::all();

        foreach ($tenants as $tenant) {
            $connectionName = $this->connectionName($tenant->id);

            $this->registerTenantConnection($tenant, $connectionName);

            if ($this->tenantHasGupaTables($connectionName)) {
                SyncMasterDataToTenant::dispatch($tenant->id);
            }

            if ($this->tenantHasTable($connectionName, 'gupa_logs')) {
                SyncLogsFromTenant::dispatch($tenant->id);
            }
        }
    }

    public function syncMasterDataToTenant(string $tenantId): void
    {
        if (! config('gupa-panel.enabled', true)) {
            return;
        }

        $tenant = BaleList::find($tenantId);

        if (! $tenant) {
            return;
        }

        $connectionName = $this->connectionName($tenant->id);
        $this->registerTenantConnection($tenant, $connectionName);

        if ($this->tenantHasTable($connectionName, 'gupa_blocked_ips')
            && config('gupa-panel.sync_blocked_ips', true)) {
            $this->syncBlockedIpsBidirectional($connectionName);
        }

        if ($this->tenantHasTable($connectionName, 'gupa_blacklists')
            && config('gupa-panel.sync_blacklists', true)) {
            $this->syncTableFromMaster($connectionName, 'gupa_blacklists', PanelBlacklist::class,
                fn ($masterRecord) => [
                    'id' => $masterRecord->id,
                    'ip' => $masterRecord->ip,
                    'reason' => $masterRecord->reason,
                    'created_at' => $masterRecord->created_at,
                ],
                'ip'
            );
        }

        if ($this->tenantHasTable($connectionName, 'gupa_whitelists')
            && config('gupa-panel.sync_whitelists', true)) {
            $this->syncTableFromMaster($connectionName, 'gupa_whitelists', PanelWhitelist::class,
                fn ($masterRecord) => [
                    'id' => $masterRecord->id,
                    'ip' => $masterRecord->ip,
                    'reason' => $masterRecord->reason,
                    'created_at' => $masterRecord->created_at,
                ],
                'ip'
            );
        }
    }

    public function syncLogsFromTenant(string $tenantId): void
    {
        if (! config('gupa-panel.enabled', true)) {
            return;
        }

        $tenant = BaleList::find($tenantId);

        if (! $tenant) {
            return;
        }

        $connectionName = $this->connectionName($tenant->id);
        $this->registerTenantConnection($tenant, $connectionName);

        if (! $this->tenantHasTable($connectionName, 'gupa_logs')) {
            return;
        }

        $syncedIds = PanelRequestLog::where('tenant_id', $tenantId)
            ->pluck('tenant_log_id');

        $query = DB::connection($connectionName)
            ->table('gupa_logs')
            ->orderBy('created_at', 'asc');

        if ($syncedIds->isNotEmpty()) {
            $query->whereNotIn('id', $syncedIds);
        }

        $tenantLogs = $query
            ->limit((int) config('gupa-panel.log_sync_batch', 1000))
            ->get();

        $synced = 0;

        foreach ($tenantLogs as $log) {
            try {
                $exists = PanelRequestLog::where('tenant_id', $tenantId)
                    ->where('tenant_log_id', $log->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                PanelRequestLog::create([
                    'tenant_id' => $tenantId,
                    'tenant_log_id' => $log->id,
                    'ip' => $log->ip,
                    'metadata' => $this->buildRequestLogMetadata($log),
                    'created_at' => $log->created_at,
                    'updated_at' => $log->updated_at ?? $log->created_at,
                ]);
                $synced++;
            } catch (QueryException $e) {
                if ($this->isDuplicateLogEntry($e)) {
                    continue;
                }

                throw $e;
            }
        }
    }

    protected function isDuplicateLogEntry(QueryException $e): bool
    {
        return str_contains($e->getMessage(), 'tenant_log_unique');
    }

    public function buildRequestLogMetadata(object $log): array
    {
        $metadata = is_string($log->metadata)
            ? (json_decode($log->metadata, true) ?? [])
            : ((array) ($log->metadata ?? []));

        foreach (['score', 'path', 'method', 'user_agent', 'status_code'] as $key) {
            $value = $log->$key ?? $metadata[$key] ?? null;

            if ($value !== null) {
                $metadata[$key] = $value;
            }
        }

        return $metadata;
    }

    public function backfillLogMetadata(?string $tenantId = null): int
    {
        if (! config('gupa-panel.enabled', true)) {
            return 0;
        }

        $tenants = $tenantId
            ? BaleList::where('id', $tenantId)->get()
            : BaleList::all();

        $updated = 0;

        foreach ($tenants as $tenant) {
            $connectionName = $this->connectionName($tenant->id);

            $this->registerTenantConnection($tenant, $connectionName);

            if (! $this->tenantHasTable($connectionName, 'gupa_logs')) {
                continue;
            }

            $updated += $this->backfillTenantLogMetadata($tenant->id, $connectionName);
        }

        return $updated;
    }

    protected function backfillTenantLogMetadata(string $tenantId, string $connectionName): int
    {
        $updated = 0;

        PanelRequestLog::where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->whereNull('metadata')
                    ->orWhere('metadata', '')
                    ->orWhere('metadata', '[]');
            })
            ->select(['id', 'tenant_log_id'])
            ->chunk(500, function ($logs) use ($connectionName, &$updated) {
                $ids = $logs->pluck('tenant_log_id')->filter()->unique()->values();

                if ($ids->isEmpty()) {
                    return;
                }

                $tenantLogs = DB::connection($connectionName)
                    ->table('gupa_logs')
                    ->whereIn('id', $ids)
                    ->get()
                    ->keyBy('id');

                foreach ($logs as $log) {
                    $tenantLog = $tenantLogs->get($log->tenant_log_id);

                    if (! $tenantLog) {
                        continue;
                    }

                    $log->update(['metadata' => $this->buildRequestLogMetadata($tenantLog)]);

                    $updated++;
                }
            });

        return $updated;
    }

    protected function syncTableFromMaster(
        string $connectionName,
        string $tenantTable,
        string $masterModelClass,
        callable $mapMasterToTenant,
        string $uniqueKey = 'ip',
    ): void {
        $masterRecords = $masterModelClass::all();

        $masterKeys = [];

        foreach ($masterRecords as $masterRecord) {
            $key = $masterRecord->$uniqueKey;
            $masterKeys[$key] = true;

            $tenantRow = DB::connection($connectionName)
                ->table($tenantTable)
                ->where($uniqueKey, $key)
                ->first();

            if (! $tenantRow) {
                DB::connection($connectionName)
                    ->table($tenantTable)
                    ->insert($mapMasterToTenant($masterRecord));

                continue;
            }

            $changes = [];

            foreach ($mapMasterToTenant($masterRecord) as $column => $value) {
                if ($column === $uniqueKey || $column === 'id') {
                    continue;
                }

                $current = $tenantRow->$column ?? null;

                if ((string) $current !== (string) $value) {
                    $changes[$column] = $value;
                }
            }

            if ($changes !== []) {
                DB::connection($connectionName)
                    ->table($tenantTable)
                    ->where($uniqueKey, $key)
                    ->update($changes);
            }
        }

        if ($masterKeys !== []) {
            DB::connection($connectionName)
                ->table($tenantTable)
                ->whereNotIn($uniqueKey, array_keys($masterKeys))
                ->delete();
        }
    }

    protected function syncBlockedIpsBidirectional(string $connectionName): void
    {
        $this->cleanupExpiredBlockedIps();

        $tenantRecords = DB::connection($connectionName)
            ->table('gupa_blocked_ips')
            ->get();

        $tenantKeys = [];

        foreach ($tenantRecords as $record) {
            if ($this->isExpiredBlockedIp($record)) {
                DB::connection($connectionName)
                    ->table('gupa_blocked_ips')
                    ->where('ip', $record->ip)
                    ->delete();

                continue;
            }

            $tenantKeys[$record->ip] = true;

            $masterRecord = PanelBlockedIp::where('ip', $record->ip)->first();

            if (! $masterRecord) {
                PanelBlockedIp::create([
                    'ip' => $record->ip,
                    'reason' => $record->reason ?? 'synced from tenant',
                    'is_permanent' => $record->is_permanent ?? false,
                    'expires_at' => $record->expires_at,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at ?? $record->created_at,
                ]);
            }
        }

        $masterRecords = PanelBlockedIp::all();

        foreach ($masterRecords as $masterRecord) {
            if (! isset($tenantKeys[$masterRecord->ip])) {
                DB::connection($connectionName)
                    ->table('gupa_blocked_ips')
                    ->insert([
                        'id' => $masterRecord->id,
                        'ip' => $masterRecord->ip,
                        'reason' => $masterRecord->reason,
                        'is_permanent' => $masterRecord->is_permanent,
                        'expires_at' => $masterRecord->expires_at,
                        'created_at' => $masterRecord->created_at,
                        'updated_at' => $masterRecord->updated_at,
                    ]);
            }
        }
    }

    /**
     * Hapus IP dari blocked list di panel dan seluruh tenant.
     *
     * Semua-atau-tidak-sama-sekali: preflight koneksi tiap tenant lebih dulu,
     * dan bila satu tenant tidak bisa dihubungi tidak ada baris yang dihapus.
     * Baris master hanya dihapus setelah semua tenant bersih, supaya sync
     * berikutnya tidak menghidupkannya lagi dari tenant yang tertinggal.
     *
     * @param  array<int, string>  $ips
     * @return array{ok: bool, reason?: string, ips: array<int, string>, tenants: array<int, string>, skipped: array<int, string>, deleted_tenant_rows: int, deleted_panel_rows: int}
     */
    public function unblockIps(array $ips, bool $whitelist = false): array
    {
        $ips = array_values(array_unique(array_filter($ips)));

        $result = [
            'ok' => false,
            'ips' => $ips,
            'tenants' => [],
            'skipped' => [],
            'deleted_tenant_rows' => 0,
            'deleted_panel_rows' => 0,
        ];

        if ($ips === []) {
            return $result + ['reason' => 'no ips given'];
        }

        if (! config('gupa-panel.enabled', true)) {
            return $result + ['reason' => 'gupa panel disabled'];
        }

        $ready = [];

        foreach (BaleList::all() as $tenant) {
            $connectionName = $this->connectionName($tenant->id);

            try {
                $this->registerTenantConnection($tenant, $connectionName);

                if (! Schema::connection($connectionName)->hasTable('gupa_blocked_ips')) {
                    $result['skipped'][] = $tenant->id;

                    continue;
                }
            } catch (\Throwable $e) {
                return $result + ['reason' => "tenant {$tenant->id} unreachable: {$e->getMessage()}"];
            }

            $ready[$tenant->id] = $connectionName;
        }

        if ($whitelist) {
            foreach ($ips as $ip) {
                PanelWhitelist::firstOrCreate(
                    ['ip' => $ip],
                    ['reason' => 'False positive — auto-whitelisted from review'],
                );
            }
        }

        $deletedTenantRows = 0;

        foreach ($ready as $tenantId => $connectionName) {
            try {
                $deletedTenantRows += DB::connection($connectionName)
                    ->table('gupa_blocked_ips')
                    ->whereIn('ip', $ips)
                    ->delete();
            } catch (\Throwable $e) {
                // Sebagian tenant sudah terhapus; master dibiarkan agar bisa diulang.
                return array_merge($result, [
                    'reason' => "tenant {$tenantId} failed: {$e->getMessage()}",
                    'tenants' => array_keys($ready),
                    'deleted_tenant_rows' => $deletedTenantRows,
                ]);
            }
        }

        $result['ok'] = true;
        $result['tenants'] = array_keys($ready);
        $result['deleted_tenant_rows'] = $deletedTenantRows;
        $result['deleted_panel_rows'] = PanelBlockedIp::whereIn('ip', $ips)->delete();

        return $result;
    }

    protected function isExpiredBlockedIp(object $record): bool
    {
        if (($record->is_permanent ?? false) || empty($record->expires_at)) {
            return false;
        }

        return strtotime($record->expires_at) < $this->retentionThreshold()->timestamp;
    }

    protected function tenantHasGupaTables(string $connectionName): bool
    {
        return $this->tenantHasTable($connectionName, 'gupa_blocked_ips')
            || $this->tenantHasTable($connectionName, 'gupa_blacklists')
            || $this->tenantHasTable($connectionName, 'gupa_whitelists');
    }

    public function cleanupExpiredBlockedIps(): int
    {
        $deleted = PanelBlockedIp::where('is_permanent', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $this->retentionThreshold())
            ->delete();

        return $deleted;
    }

    protected function retentionThreshold(): Carbon
    {
        $days = max(0, (int) config('gupa-panel.blocked_ip_retention_days', 0));

        return now()->subDays($days);
    }

    protected function connectionName(string $baleId): string
    {
        return 'bale_'.str_replace('-', '_', $baleId);
    }

    protected function tenantHasTable(string $connectionName, string $table): bool
    {
        try {
            return Schema::connection($connectionName)->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function registerTenantConnection(BaleList $tenant, string $connectionName): void
    {
        Config::set("database.connections.{$connectionName}", [
            'driver' => config('gupa-panel.tenant_driver', 'mysql'),
            'host' => $tenant->database_host,
            'port' => $tenant->database_port ?? 3306,
            'database' => $tenant->database_name,
            'username' => $tenant->database_username,
            'password' => $tenant->database_password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ]);
    }

    public function getLastSyncTime(): ?string
    {
        return null;
    }
}
