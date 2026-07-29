<?php

namespace Bale\GupaPanel\Livewire\Pages;

use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelWhitelist;
use Livewire\Component;
use Livewire\WithPagination;

class BlacklistIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $ip = '';

    public string $reason = '';

    public bool $showForm = false;

    protected $rules = [
        'ip' => 'required|string|max:255',
        'reason' => 'nullable|string|max:500',
    ];

    public function add(): void
    {
        $this->validate();

        PanelWhitelist::where('ip', $this->ip)->delete();

        PanelBlacklist::create([
            'ip' => $this->ip,
            'reason' => $this->reason,
        ]);

        $this->reset(['ip', 'reason', 'showForm']);
        session()->flash('message', 'IP added to blacklist.');
    }

    public function delete(string $id): void
    {
        PanelBlacklist::findOrFail($id)->delete();
    }

    public function render()
    {
        $query = PanelBlacklist::latest('created_at');

        if ($this->search) {
            $query->where('ip', 'like', "%{$this->search}%");
        }

        return view('gupa-panel::livewire.pages.blacklist-index', [
            'blacklists' => $query->paginate(20),
        ])->layout('core::layouts.app');
    }
}
