<?php

namespace Bale\GupaPanel\Livewire\Pages;

use Bale\GupaPanel\Models\PanelBlockedIp;
use Bale\GupaPanel\Models\PanelWhitelist;
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
        $whitelisted = 0;
        $alreadyWhitelisted = 0;

        foreach ($this->selected as $id) {
            $blocked = PanelBlockedIp::find($id);

            if (! $blocked) {
                continue;
            }

            $entry = PanelWhitelist::firstOrCreate(
                ['ip' => $blocked->ip],
                ['reason' => 'False positive — auto-whitelisted from review'],
            );

            if ($entry->wasRecentlyCreated) {
                $whitelisted++;
            } else {
                $alreadyWhitelisted++;
            }

            $blocked->delete();
        }

        $this->selected = [];
        session()->flash('message', $this->whitelistMessage($whitelisted, $alreadyWhitelisted));

        $this->mount(app(FalsePositiveDetector::class));
    }

    protected function whitelistMessage(int $whitelisted, int $alreadyWhitelisted): string
    {
        if ($whitelisted > 0 && $alreadyWhitelisted > 0) {
            return "{$whitelisted} IP(s) whitelisted and unblocked. {$alreadyWhitelisted} IP(s) were already whitelisted.";
        }

        if ($alreadyWhitelisted > 0) {
            return "{$alreadyWhitelisted} IP(s) were already whitelisted and have been unblocked.";
        }

        return 'Selected IPs have been whitelisted and unblocked.';
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
