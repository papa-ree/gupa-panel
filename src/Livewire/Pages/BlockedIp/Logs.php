<?php

namespace Bale\GupaPanel\Livewire\Pages\BlockedIp;

use Bale\GupaPanel\Models\PanelBlockedIp;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('core::layouts.app')]
#[Title('Request Logs')]
class Logs extends Component
{
    public string $ip = '';

    public PanelBlockedIp $blocked;

    public function mount(string $ip): void
    {
        $this->ip = $ip;

        $blocked = PanelBlockedIp::where('ip', $ip)->first();

        abort_unless($blocked, 404);

        $this->blocked = $blocked;
    }

    public function render()
    {
        return view('gupa-panel::livewire.pages.blocked-ip.logs');
    }
}
