<?php

namespace Bale\GupaPanel\Livewire\Pages\Sync;

use Bale\GupaPanel\Jobs\SyncBlacklistsToTenant;
use Bale\GupaPanel\Jobs\SyncWhitelistsToTenant;
use Bale\GupaPanel\Jobs\SyncBlockedIpsToTenant;
use Bale\GupaPanel\Jobs\SyncLogsFromTenant;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelRequestLog;
use Bale\GupaPanel\Models\PanelWhitelist;
use Bale\GupaPanel\Services\GupaSyncService;
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
        $this->dispatchBrowserEvent('toast', [
            'message' => 'Sync jobs dispatched for all tenants.',
            'type' => 'success',
        ]);

        SyncBlacklistsToTenant::dispatch();
        SyncWhitelistsToTenant::dispatch();
        SyncBlockedIpsToTenant::dispatch();
    }

    public function syncBlacklists(): void
    {
        SyncBlacklistsToTenant::dispatch();
        $this->dispatchBrowserEvent('toast', ['message' => 'Blacklist sync dispatched.', 'type' => 'success']);
    }

    public function syncWhitelists(): void
    {
        SyncWhitelistsToTenant::dispatch();
        $this->dispatchBrowserEvent('toast', ['message' => 'Whitelist sync dispatched.', 'type' => 'success']);
    }

    public function syncBlockedIps(): void
    {
        SyncBlockedIpsToTenant::dispatch();
        $this->dispatchBrowserEvent('toast', ['message' => 'Blocked IPs sync dispatched.', 'type' => 'success']);
    }

    public function syncLogs(): void
    {
        SyncLogsFromTenant::dispatch();
        $this->dispatchBrowserEvent('toast', ['message' => 'Log sync dispatched.', 'type' => 'success']);
    }

    public function render()
    {
        return view('gupa-panel::livewire.pages.sync.index');
    }
}