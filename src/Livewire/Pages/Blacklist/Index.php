<?php

namespace Bale\GupaPanel\Livewire\Pages\Blacklist;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('core::layouts.app')]
#[Title('Blacklist')]
class Index extends Component
{
    public function render()
    {
        return view('gupa-panel::livewire.pages.blacklist.index');
    }
}
