<?php

namespace Bale\GupaPanel\Livewire\Pages;

use Bale\GupaPanel\Models\PanelBlockedIp;
use Livewire\Component;
use Livewire\WithPagination;

class BlockedIpIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    public function unblock(string $id): void
    {
        $blocked = PanelBlockedIp::findOrFail($id);
        $blocked->delete();

        session()->flash('message', "IP {$blocked->ip} has been unblocked.");
    }

    public function render()
    {
        $query = PanelBlockedIp::latest();

        if ($this->search) {
            $query->where('ip', 'like', "%{$this->search}%");
        }

        if ($this->filterStatus === 'temporary') {
            $query->where('is_permanent', false);
        } elseif ($this->filterStatus === 'permanent') {
            $query->where('is_permanent', true);
        }

        return view('gupa-panel::livewire.pages.blocked-ip-index', [
            'blockedIps' => $query->paginate(20),
        ])->layout('core::layouts.app');
    }
}
