<?php

namespace Bale\GupaPanel\Livewire\Pages\Blacklist;

use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelWhitelist;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class Form extends Component
{
    public ?string $blacklistId = null;

    public string $ip = '';

    public string $reason = '';

    protected $rules = [
        'ip' => 'required|string|max:255',
        'reason' => 'nullable|string|max:500',
    ];

    public function mount(?string $id = null): void
    {
        if ($id) {
            $this->blacklistId = $id;
            $blacklist = PanelBlacklist::findOrFail($id);
            $this->ip = $blacklist->ip;
            $this->reason = $blacklist->reason ?? '';
        }
    }

    public function save(): void
    {
        $this->validate();

        PanelWhitelist::where('ip', $this->ip)->delete();

        if ($this->blacklistId) {
            $blacklist = PanelBlacklist::findOrFail($this->blacklistId);
            $blacklist->update([
                'ip' => $this->ip,
                'reason' => $this->reason,
            ]);
        } else {
            PanelBlacklist::create([
                'ip' => $this->ip,
                'reason' => $this->reason,
            ]);
        }

        $this->dispatch('toast', message: 'IP saved to blacklist.', type: 'success');
        $this->redirectRoute('gupa-panel.blacklist', navigate: true);
    }

    public function render()
    {
        return view('gupa-panel::livewire.pages.blacklist.form');
    }
}
