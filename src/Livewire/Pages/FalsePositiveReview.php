<?php

namespace Bale\GupaPanel\Livewire\Pages;

use Bale\GupaPanel\Jobs\UnblockIpsOnTenants;
use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Services\FalsePositiveDetector;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('core::layouts.app')]
#[Title('False Positive Review')]
class FalsePositiveReview extends Component
{
    public array $candidates = [];

    public array $selected = [];

    public function mount(FalsePositiveDetector $fpDetector): void
    {
        $fpResults = $fpDetector->getFalsePositives(20);

        $this->candidates = array_map(function ($item) {
            $blockedIp = $item['blocked_ip'];

            return [
                'id' => $blockedIp->id,
                'ip' => $blockedIp->ip,
                'reason' => $blockedIp->reason,
                'is_permanent' => $blockedIp->is_permanent,
                'created_at' => $blockedIp->created_at->toDateTimeString(),
                'analysis' => $item['analysis'],
            ];
        }, $fpResults);
    }

    public function whitelistSelected(): void
    {
        $this->unblockSelected(whitelist: true);
    }

    public function unblockOnly(): void
    {
        $this->unblockSelected(whitelist: false);
    }

    protected function unblockSelected(bool $whitelist): void
    {
        $ips = PanelBlockedIp::whereIn('id', $this->selected)->pluck('ip')->all();

        if ($ips === []) {
            session()->flash('message', 'No blocked IP selected.');

            return;
        }

        $blacklisted = PanelBlacklist::whereIn('ip', $ips)->pluck('ip')->all();

        if ($blacklisted !== []) {
            session()->flash('error', 'Aborted: remove these IPs from the Blacklist first — '.implode(', ', $blacklisted));

            return;
        }

        UnblockIpsOnTenants::dispatch($ips, $whitelist);

        $this->selected = [];
        session()->flash('message', $whitelist
            ? 'Whitelist & unblock scheduled for the selected IPs.'
            : 'Unblock scheduled for the selected IPs.');

        $this->mount(app(FalsePositiveDetector::class));
    }

    public function render()
    {
        return view('gupa-panel::livewire.pages.false-positive-review')
            ->layout('core::layouts.app');
    }
}
