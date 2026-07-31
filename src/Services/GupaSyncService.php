<?php

namespace Bale\GupaPanel\Services;

use Bale\Cms\Models\BaleList;
use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Models\PanelWhitelist;
use Bale\GupaPanel\Models\PanelRequestLog;
use Bale\GupaPanel\Jobs\SyncMasterDataToTenant;
use Bale\GupaPanel\Jobs\SyncLogsFromTenant;
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

        $this->cleanupExpiredBlockedIps();

        if ($this->tenantHasTable($connectionName, 'gupa_blocked_ips')
            && config('gupa-panel.sync_blocked_ips', true)) {
            $this->syncTableBidirectional($connectionName, 'gupa_blocked_ips', PanelBlockedIp::class,
                fn ($record) => [
                    'ip' => $record->ip,
                    'reason' => $record->reason ?? 'synced from tenant',
                    'is_permanent' => $record->is_permanent ?? false,
                    'expires_at' => $record->expires_at,
                    'created_at' => $record->created_at,
                    'updated_at' => $record->updated_at ?? $record->created_at,
                ],
                fn ($masterRecord) => [
                    'id' => $masterRecord->id,
                    'ip' => $masterRecord->ip,
                    'reason' => $masterRecord->reason,
                    'is_permanent' => $masterRecord->is_permanent,
                    'expires_at' => $masterRecord->expires_at,
                    'created_at' => $masterRecord->created_at,
                    'updated_at' => $masterRecord->updated_at,
                ],
                'ip'
            );
        }

        if ($this->tenantHasTable($connectionName, 'gupa_blacklists')
            && config('gupa-panel.sync_blacklists', true)) {
            $this->syncTableBidirectional($connectionName, 'gupa_blacklists', PanelBlacklist::class,
                fn ($record) => [
                    'ip' => $record->ip,
                    'reason' => $record->reason ?? 'synced from tenant',
                    'created_at' => $record->created_at,
                ],
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
            $this->syncTableBidirectional($connectionName, 'gupa_whitelists', PanelWhitelist::class,
                fn ($record) => [
                    'ip' => $record->ip,
                    'reason' => $record->reason ?? 'synced from tenant',
                    'created_at' => $record->created_at,
                ],
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

        $tenantLogs = DB::connection($connectionName)
            ->table('gupa_logs')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $synced = 0;

        foreach ($tenantLogs as $log) {
            $exists = PanelRequestLog::where('tenant_id', $tenantId)
                ->where('tenant_log_id', $log->id)
                ->exists();

            if (! $exists) {
                PanelRequestLog::create([
                    'tenant_id' => $tenantId,
                    'tenant_log_id' => $log->id,
                    'ip' => $log->ip,
                    'metadata' => is_string($log->metadata) ? json_decode($log->metadata, true) : (array) $log->metadata,
                    'created_at' => $log->created_at,
                    'updated_at' => $log->updated_at ?? $log->created_at,
                ]);
                $synced++;
            }
        }
    }

    protected function syncTableBidirectional(
        string $connectionName,
        string $tenantTable,
        string $masterModelClass,
        callable $mapTenantToMaster,
        callable $mapMasterToTenant,
        string $uniqueKey = 'ip',
    ): void {
        $tenantRecords = DB::connection($connectionName)
            ->table($tenantTable)
            ->get();

        $tenantKeys = [];

        foreach ($tenantRecords as $record) {
            $key = $record->$uniqueKey;
            $tenantKeys[$key] = true;

            $masterRecord = $masterModelClass::where($uniqueKey, $key)->first();

            if (! $masterRecord) {
                $masterModelClass::create($mapTenantToMaster($record));
            }
        }

        $masterRecords = $masterModelClass::all();

        foreach ($masterRecords as $masterRecord) {
            $key = $masterRecord->$uniqueKey;

            if (! isset($tenantKeys[$key])) {
                DB::connection($connectionName)
                    ->table($tenantTable)
                    ->insert($mapMasterToTenant($masterRecord));
            }
        }
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
            ->where('expires_at', '<', now())
            ->delete();

        return $deleted;
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
            'driver' => 'mysql',
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