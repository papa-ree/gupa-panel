<?php

namespace Bale\GupaPanel\Livewire\Pages\BlockedIp;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('core::layouts.app')]
#[Title('Blocked IPs')]
class Index extends Component
{
    public function render()
    {
        return view('gupa-panel::livewire.pages.blocked-ip.index');
    }
}
