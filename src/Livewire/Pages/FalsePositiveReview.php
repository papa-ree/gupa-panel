<?php

namespace Bale\GupaPanel\Livewire\Pages;

use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Models\PanelWhitelist;
use Bale\GupaPanel\Services\FalsePositiveDetector;
use Livewire\Component;

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
        foreach ($this->selected as $id) {
            $blocked = PanelBlockedIp::find($id);

            if (! $blocked) {
                continue;
            }

            PanelWhitelist::create([
                'ip' => $blocked->ip,
                'reason' => 'False positive — auto-whitelisted from review',
            ]);

            $blocked->delete();
        }

        $this->selected = [];
        session()->flash('message', 'Selected IPs have been whitelisted and unblocked.');

        $this->mount(app(FalsePositiveDetector::class));
    }

    public function unblockOnly(): void
    {
        foreach ($this->selected as $id) {
            $blocked = PanelBlockedIp::find($id);

            if ($blocked) {
                $blocked->delete();
            }
        }

        $this->selected = [];
        session()->flash('message', 'Selected IPs have been unblocked.');

        $this->mount(app(FalsePositiveDetector::class));
    }

    public function render()
    {
        return view('gupa-panel::livewire.pages.false-positive-review')
            ->layout('core::layouts.app');
    }
}
