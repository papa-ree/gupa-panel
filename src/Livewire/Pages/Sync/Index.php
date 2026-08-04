<?php

namespace Bale\GupaPanel\Livewire\Pages\Sync;

use Bale\Cms\Models\BaleList;
use Bale\GupaPanel\Jobs\SyncAllToTenants;
use Bale\GupaPanel\Jobs\SyncLogsFromTenant;
use Bale\GupaPanel\Jobs\SyncMasterDataToTenant;
use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Models\PanelRequestLog;
use Bale\GupaPanel\Models\PanelWhitelist;
use Bale\GupaPanel\Services\GupaSyncService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Facades\Activity;

#[Layout('core::layouts.app')]
#[Title('Sync')]
class Index extends Component
{
    public array $syncStats = [];

    public array $recentJobs = [];

    public string $selectedTenant = '';

    public string $syncType = 'all';

    public function mount(GupaSyncService $syncService): void
    {
        $this->loadStats($syncService);
    }

    public function loadStats(GupaSyncService $syncService): void
    {
        $this->syncStats = [
            'blacklists' => PanelBlacklist::count(),
            'whitelists' => PanelWhitelist::count(),
            'blocked_ips' => PanelBlockedIp::count(),
            'logs' => PanelRequestLog::count(),
            'last_sync' => $syncService->getLastSyncTime(),
        ];
    }

    public function syncAll(): void
    {
        SyncAllToTenants::dispatch();
        $this->logSyncAction('sync_all', 'User initiated full sync to all tenants');
        $this->dispatch('toast', message: 'Sync jobs dispatched for all tenants.', type: 'success');
    }

    public function syncPanelData(): void
    {
        $tenants = BaleList::all();
        $dispatched = 0;

        foreach ($tenants as $tenant) {
            SyncMasterDataToTenant::dispatch($tenant->id);
            $dispatched++;
        }

        $this->logSyncAction('sync_panel_data', 'User initiated panel data sync (blacklist, whitelist, blocked IPs)');
        $this->dispatch('toast', message: "Panel data sync dispatched for {$dispatched} tenant(s).", type: 'success');
    }

    public function syncLogs(): void
    {
        $tenants = BaleList::all();
        $dispatched = 0;

        foreach ($tenants as $tenant) {
            SyncLogsFromTenant::dispatch($tenant->id);
            $dispatched++;
        }

        $this->logSyncAction('sync_logs', 'User initiated log sync from all tenants');
        $this->dispatch('toast', message: "Log sync dispatched for {$dispatched} tenant(s).", type: 'success');
    }

    protected function logSyncAction(string $type, string $description): void
    {
        Activity::inLog('gupa-panel-sync')
            ->causedByAnonymous()
            ->withProperties([
                'logged_by' => 'user',
                'type' => $type,
                'tenants_count' => BaleList::count(),
            ])
            ->log($description);
    }

    public function render()
    {
        return view('gupa-panel::livewire.pages.sync.index');
    }
}
