<?php

namespace Bale\GupaPanel\Livewire\Pages\Whitelist;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('core::layouts.app')]
#[Title('Whitelist')]
class Index extends Component
{
    public function render()
    {
        return view('gupa-panel::livewire.pages.whitelist.index');
    }
}
