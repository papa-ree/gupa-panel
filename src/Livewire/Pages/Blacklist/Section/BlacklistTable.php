<?php

namespace Bale\GupaPanel\Livewire\Pages\Blacklist\Section;

use Bale\Core\Traits\HasDeleteOption;
use Livewire\Component;

class BlacklistTable extends Component
{
    use HasDeleteOption;

    protected string $modelClass = \Bale\GupaPanel\Models\PanelBlacklist::class;

    public function render()
    {
        return view('gupa-panel::livewire.pages.blacklist.section.blacklist-table');
    }
}
