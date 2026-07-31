<?php

namespace Bale\GupaPanel\Livewire\Pages\KnownCrawler;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('core::layouts.app')]
class Index extends Component
{
    public function render()
    {
        return view('gupa-panel::livewire.pages.known-crawler.index');
    }
}