<?php

namespace Bale\GupaPanel\Livewire\Pages\Blacklist\Section;

use Bale\Core\Traits\HasDeleteOption;
use Bale\GupaPanel\Models\PanelBlacklist;
use Livewire\Component;

class BlacklistTable extends Component
{
    use HasDeleteOption;

    protected string $modelClass = PanelBlacklist::class;

    public function render()
    {
        return view('gupa-panel::livewire.pages.blacklist.section.blacklist-table');
    }
}
