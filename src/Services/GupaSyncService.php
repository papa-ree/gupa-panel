<?php

namespace Bale\GupaPanel\Services;

use Bale\Cms\Models\BaleList;
use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Models\PanelWhitelist;
use Bale\GupaPanel\Models\PanelRequestLog;
use Bale\GupaPanel\Jobs\SyncBlacklistToTenant;
use Bale\GupaPanel\Jobs\SyncBlockedIpToTenant;
use Bale\GupaPanel\Jobs\SyncWhitelistToTenant;
use Bale\GupaPanel\Jobs\SyncLogsFromTenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Facades\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;

class GupaSyncService
{
    public function syncAll(): void
    {
        $tenants = BaleList::all();
        $dispatched = 0;

        foreach ($tenants as $tenant) {
            $connectionName = $this->connectionName($tenant->id);

            $this->registerTenantConnection($tenant, $connectionName);

            if ($this->tenantHasTable($connectionName, 'gupa_blocked_ips')
                && config('gupa-panel.sync_blocked_ips', true)) {
                SyncBlockedIpToTenant::dispatch($tenant->id);
                $dispatched++;
            }

            if ($this->tenantHasTable($connectionName, 'gupa_blacklists')
                && config('gupa-panel.sync_blacklists', true)) {
                SyncBlacklistToTenant::dispatch($tenant->id);
                $dispatched++;
            }

            if ($this->tenantHasTable($connectionName, 'gupa_whitelists')
                && config('gupa-panel.sync_whitelists', true)) {
                SyncWhitelistToTenant::dispatch($tenant->id);
                $dispatched++;
            }

            if ($this->tenantHasTable($connectionName, 'gupa_logs')) {
                SyncLogsFromTenant::dispatch($tenant->id);
                $dispatched++;
            }
        }

        activity('gupa-panel-sync')
            ->causedByAnonymous()
            ->withProperties([
                'logged_by' => 'system',
                'tenants_count' => $tenants->count(),
                'jobs_dispatched' => $dispatched,
            ])
            ->log("GupaPanel sync dispatched {$dispatched} jobs to {$tenants->count()} tenants");
    }

    public function syncBlockedIpsToTenant(string $tenantId): void
    {
        $tenant = BaleList::find($tenantId);

        if (! $tenant) {
            return;
        }

        $connectionName = $this->connectionName($tenant->id);
        $this->registerTenantConnection($tenant, $connectionName);

        if (! $this->tenantHasTable($connectionName, 'gupa_blocked_ips')) {
            return;
        }

        $this->cleanupExpiredBlockedIps();

        $tenantBlockedIps = DB::connection($connectionName)
            ->table('gupa_blocked_ips')
            ->get();

        $syncedFromTenant = 0;
        $tenantIps = [];

        foreach ($tenantBlockedIps as $record) {
            $tenantIps[$record->ip] = true;

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
                $syncedFromTenant++;
            } else {
                $masterRecord->update([
                    'reason' => $record->reason ?? $masterRecord->reason,
                    'is_permanent' => $record->is_permanent ?? $masterRecord->is_permanent,
                    'expires_at' => $record->expires_at,
                    'updated_at' => $record->updated_at ?? $masterRecord->updated_at,
                ]);
            }
        }

        $masterBlockedIps = PanelBlockedIp::all();
        $syncedToTenant = 0;

        foreach ($masterBlockedIps as $masterRecord) {
            if (! isset($tenantIps[$masterRecord->ip])) {
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
                $syncedToTenant++;
            }
        }

        activity('gupa-panel-sync')
            ->causedByAnonymous()
            ->withProperties([
                'logged_by' => 'system',
                'tenant_id' => $tenantId,
                'type' => 'blocked_ips',
                'synced_from_tenant' => $syncedFromTenant,
                'synced_to_tenant' => $syncedToTenant,
            ])
            ->log("Blocked IPs sync for tenant {$tenantId}: {$syncedFromTenant} from tenant, {$syncedToTenant} to tenant");
    }

    public function syncBlacklistsToTenant(string $tenantId): void
    {
        $tenant = BaleList::find($tenantId);

        if (! $tenant) {
            return;
        }

        $connectionName = $this->connectionName($tenant->id);
        $this->registerTenantConnection($tenant, $connectionName);

        if (! $this->tenantHasTable($connectionName, 'gupa_blacklists')) {
            return;
        }

        $tenantBlacklists = DB::connection($connectionName)
            ->table('gupa_blacklists')
            ->get();

        $syncedFromTenant = 0;
        $tenantIps = [];

        foreach ($tenantBlacklists as $record) {
            $tenantIps[$record->ip] = true;

            $masterRecord = PanelBlacklist::where('ip', $record->ip)->first();

            if (! $masterRecord) {
                PanelBlacklist::create([
                    'ip' => $record->ip,
                    'reason' => $record->reason ?? 'synced from tenant',
                    'created_at' => $record->created_at,
                ]);
                $syncedFromTenant++;
            }
        }

        $masterBlacklists = PanelBlacklist::all();
        $syncedToTenant = 0;

        foreach ($masterBlacklists as $masterRecord) {
            if (! isset($tenantIps[$masterRecord->ip])) {
                DB::connection($connectionName)
                    ->table('gupa_blacklists')
                    ->insert([
                        'id' => $masterRecord->id,
                        'ip' => $masterRecord->ip,
                        'reason' => $masterRecord->reason,
                        'created_at' => $masterRecord->created_at,
                    ]);
                $syncedToTenant++;
            }
        }

        activity('gupa-panel-sync')
            ->causedByAnonymous()
            ->withProperties([
                'logged_by' => 'system',
                'tenant_id' => $tenantId,
                'type' => 'blacklists',
                'synced_from_tenant' => $syncedFromTenant,
                'synced_to_tenant' => $syncedToTenant,
            ])
            ->log("Blacklists sync for tenant {$tenantId}: {$syncedFromTenant} from tenant, {$syncedToTenant} to tenant");
    }

    public function syncWhitelistsToTenant(string $tenantId): void
    {
        $tenant = BaleList::find($tenantId);

        if (! $tenant) {
            return;
        }

        $connectionName = $this->connectionName($tenant->id);
        $this->registerTenantConnection($tenant, $connectionName);

        if (! $this->tenantHasTable($connectionName, 'gupa_whitelists')) {
            return;
        }

        $tenantWhitelists = DB::connection($connectionName)
            ->table('gupa_whitelists')
            ->get();

        $syncedFromTenant = 0;
        $tenantIps = [];

        foreach ($tenantWhitelists as $record) {
            $tenantIps[$record->ip] = true;

            $masterRecord = PanelWhitelist::where('ip', $record->ip)->first();

            if (! $masterRecord) {
                PanelWhitelist::create([
                    'ip' => $record->ip,
                    'reason' => $record->reason ?? 'synced from tenant',
                    'created_at' => $record->created_at,
                ]);
                $syncedFromTenant++;
            }
        }

        $masterWhitelists = PanelWhitelist::all();
        $syncedToTenant = 0;

        foreach ($masterWhitelists as $masterRecord) {
            if (! isset($tenantIps[$masterRecord->ip])) {
                DB::connection($connectionName)
                    ->table('gupa_whitelists')
                    ->insert([
                        'id' => $masterRecord->id,
                        'ip' => $masterRecord->ip,
                        'reason' => $masterRecord->reason,
                        'created_at' => $masterRecord->created_at,
                    ]);
                $syncedToTenant++;
            }
        }

        activity('gupa-panel-sync')
            ->causedByAnonymous()
            ->withProperties([
                'logged_by' => 'system',
                'tenant_id' => $tenantId,
                'type' => 'whitelists',
                'synced_from_tenant' => $syncedFromTenant,
                'synced_to_tenant' => $syncedToTenant,
            ])
            ->log("Whitelists sync for tenant {$tenantId}: {$syncedFromTenant} from tenant, {$syncedToTenant} to tenant");
    }

    public function syncLogsFromTenant(string $tenantId): void
    {
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

        if ($synced > 0) {
            activity('gupa-panel-sync')
                ->causedByAnonymous()
                ->withProperties([
                    'logged_by' => 'system',
                    'tenant_id' => $tenantId,
                    'type' => 'request_logs',
                    'count' => $synced,
                ])
                ->log("Synced {$synced} request logs from tenant {$tenantId}");
        }
    }

    public function cleanupExpiredBlockedIps(): int
    {
        $deleted = PanelBlockedIp::where('is_permanent', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        if ($deleted > 0) {
            activity('gupa-panel-sync')
                ->causedByAnonymous()
                ->withProperties([
                    'logged_by' => 'system',
                    'type' => 'cleanup_expired_blocked_ips',
                    'count' => $deleted,
                ])
                ->log("Cleaned up {$deleted} expired blocked IPs");
        }

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
        $lastSync = ActivityModel::where('log_name', 'gupa-panel-sync')
            ->whereNotNull('created_at')
            ->latest('created_at')
            ->first();

        return $lastSync?->created_at?->toIso8601String();
    }
}