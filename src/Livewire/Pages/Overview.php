<?php

namespace Bale\GupaPanel\Livewire\Pages;

use Bale\GupaPanel\Models\PanelRequestLog;
use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Models\PanelWhitelist;
use Bale\GupaPanel\Services\FalsePositiveDetector;
use Livewire\Component;

class Overview extends Component
{
    public array $stats = [];

    public array $recentLogs = [];

    public array $recentFalsePositives = [];

    public function mount(FalsePositiveDetector $fpDetector): void
    {
        $this->stats = [
            'total_blocked' => PanelBlockedIp::count(),
            'temporary_blocked' => PanelBlockedIp::where('is_permanent', false)->count(),
            'permanent_blocked' => PanelBlockedIp::where('is_permanent', true)->count(),
            'total_whitelisted' => PanelWhitelist::count(),
            'total_blacklisted' => PanelBlacklist::count(),
            'total_logs' => PanelRequestLog::count(),
            'logs_today' => PanelRequestLog::whereDate('created_at', today())->count(),
        ];

        $this->recentLogs = PanelRequestLog::latest()->limit(10)->get()->toArray();

        $this->recentFalsePositives = $fpDetector->getFalsePositives(5);
    }

    public function render()
    {
        return view('gupa-panel::livewire.pages.overview')
            ->layout('core::layouts.app');
    }
}
