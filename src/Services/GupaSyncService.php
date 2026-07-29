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

        $masterData = PanelBlockedIp::all();
        $synced = 0;

        foreach ($masterData as $record) {
            DB::connection($connectionName)
                ->table('gupa_blocked_ips')
                ->updateOrInsert(
                    ['id' => $record->id],
                    [
                        'ip' => $record->ip,
                        'reason' => $record->reason,
                        'is_permanent' => $record->is_permanent,
                        'expires_at' => $record->expires_at,
                        'created_at' => $record->created_at,
                        'updated_at' => $record->updated_at,
                    ]
                );
            $synced++;
        }

        activity('gupa-panel-sync')
            ->causedByAnonymous()
            ->withProperties([
                'logged_by' => 'system',
                'tenant_id' => $tenantId,
                'type' => 'blocked_ips',
                'count' => $synced,
            ])
            ->log("Synced {$synced} blocked IPs to tenant {$tenantId}");
    }

    public function syncBlacklistsToTenant(string $tenantId): void
    {
        $tenant = BaleList::find($tenantId);

        if (! $tenant) {
            return;
        }

        $connectionName = $this->connectionName($tenant->id);
        $this->registerTenantConnection($tenant, $connectionName);

        $masterData = PanelBlacklist::all();
        $synced = 0;

        foreach ($masterData as $record) {
            DB::connection($connectionName)
                ->table('gupa_blacklists')
                ->updateOrInsert(
                    ['id' => $record->id],
                    [
                        'ip' => $record->ip,
                        'created_at' => $record->created_at,
                    ]
                );
            $synced++;
        }

        activity('gupa-panel-sync')
            ->causedByAnonymous()
            ->withProperties([
                'logged_by' => 'system',
                'tenant_id' => $tenantId,
                'type' => 'blacklists',
                'count' => $synced,
            ])
            ->log("Synced {$synced} blacklists to tenant {$tenantId}");
    }

    public function syncWhitelistsToTenant(string $tenantId): void
    {
        $tenant = BaleList::find($tenantId);

        if (! $tenant) {
            return;
        }

        $connectionName = $this->connectionName($tenant->id);
        $this->registerTenantConnection($tenant, $connectionName);

        $masterData = PanelWhitelist::all();
        $synced = 0;

        foreach ($masterData as $record) {
            DB::connection($connectionName)
                ->table('gupa_whitelists')
                ->updateOrInsert(
                    ['id' => $record->id],
                    [
                        'ip' => $record->ip,
                        'created_at' => $record->created_at,
                    ]
                );
            $synced++;
        }

        activity('gupa-panel-sync')
            ->causedByAnonymous()
            ->withProperties([
                'logged_by' => 'system',
                'tenant_id' => $tenantId,
                'type' => 'whitelists',
                'count' => $synced,
            ])
            ->log("Synced {$synced} whitelists to tenant {$tenantId}");
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
                ->where('ip', $log->ip)
                ->where('created_at', $log->created_at)
                ->exists();

            if (! $exists) {
                PanelRequestLog::create([
                    'id' => $log->id,
                    'tenant_id' => $tenantId,
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
}
