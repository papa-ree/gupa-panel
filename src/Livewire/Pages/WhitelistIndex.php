<?php

namespace Bale\GupaPanel\Livewire\Pages;

use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelWhitelist;
use Livewire\Component;
use Livewire\WithPagination;

class WhitelistIndex extends Component
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

        PanelBlacklist::where('ip', $this->ip)->delete();

        PanelWhitelist::create([
            'ip' => $this->ip,
            'reason' => $this->reason,
        ]);

        $this->reset(['ip', 'reason', 'showForm']);
        session()->flash('message', 'IP added to whitelist.');
    }

    public function delete(string $id): void
    {
        PanelWhitelist::findOrFail($id)->delete();
    }

    public function render()
    {
        $query = PanelWhitelist::latest('created_at');

        if ($this->search) {
            $query->where('ip', 'like', "%{$this->search}%");
        }

        return view('gupa-panel::livewire.pages.whitelist-index', [
            'whitelists' => $query->paginate(20),
        ])->layout('core::layouts.app');
    }
}
