<?php

namespace Bale\GupaPanel\Livewire\Pages\Whitelist;

use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelWhitelist;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class Form extends Component
{
    public ?string $whitelistId = null;

    public string $ip = '';

    public string $reason = '';

    public function rules(): array
    {
        return [
            'ip' => [
                'required',
                'string',
                'max:255',
                Rule::unique('gupa_panel_whitelists', 'ip')->ignore($this->whitelistId),
            ],
            'reason' => 'nullable|string|max:500',
        ];
    }

    public function mount(?string $id = null): void
    {
        if ($id) {
            $this->whitelistId = $id;
            $whitelist = PanelWhitelist::findOrFail($id);
            $this->ip = $whitelist->ip;
            $this->reason = $whitelist->reason ?? '';
        }
    }

    public function save(): void
    {
        $this->validate();

        PanelBlacklist::where('ip', $this->ip)->delete();

        if ($this->whitelistId) {
            $whitelist = PanelWhitelist::findOrFail($this->whitelistId);
            $whitelist->update([
                'ip' => $this->ip,
                'reason' => $this->reason,
            ]);
        } else {
            PanelWhitelist::create([
                'ip' => $this->ip,
                'reason' => $this->reason,
            ]);
        }

        $this->dispatch('toast', message: 'IP saved to whitelist.', type: 'success');
        $this->redirectRoute('gupa-panel.whitelist', navigate: true);
    }

    public function render()
    {
        return view('gupa-panel::livewire.pages.whitelist.form');
    }
}
