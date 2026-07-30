<?php

namespace Bale\GupaPanel\Livewire\Pages\Sync;

use Bale\GupaPanel\Jobs\SyncAllToTenants;
use Bale\GupaPanel\Jobs\SyncBlacklistToTenant;
use Bale\GupaPanel\Jobs\SyncBlockedIpToTenant;
use Bale\GupaPanel\Jobs\SyncLogsFromTenant;
use Bale\GupaPanel\Jobs\SyncWhitelistToTenant;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelRequestLog;
use Bale\GupaPanel\Models\PanelWhitelist;
use Bale\GupaPanel\Services\GupaSyncService;
use Bale\Cms\Models\BaleList;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
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
        $this->dispatch('toast', message: 'Sync jobs dispatched for all tenants.', type: 'success');
    }

    public function syncBlacklists(): void
    {
        $tenants = BaleList::all();
        foreach ($tenants as $tenant) {
            SyncBlacklistToTenant::dispatch($tenant->id);
        }
        $this->dispatch('toast', message: 'Blacklist sync dispatched.', type: 'success');
    }

    public function syncWhitelists(): void
    {
        $tenants = BaleList::all();
        foreach ($tenants as $tenant) {
            SyncWhitelistToTenant::dispatch($tenant->id);
        }
        $this->dispatch('toast', message: 'Whitelist sync dispatched.', type: 'success');
    }

    public function syncBlockedIps(): void
    {
        $tenants = BaleList::all();
        foreach ($tenants as $tenant) {
            SyncBlockedIpToTenant::dispatch($tenant->id);
        }
        $this->dispatch('toast', message: 'Blocked IPs sync dispatched.', type: 'success');
    }

    public function syncLogs(): void
    {
        $tenants = BaleList::all();
        foreach ($tenants as $tenant) {
            SyncLogsFromTenant::dispatch($tenant->id);
        }
        $this->dispatch('toast', message: 'Log sync dispatched.', type: 'success');
    }

    public function render()
    {
        return view('gupa-panel::livewire.pages.sync.index');
    }
}