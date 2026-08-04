<?php

namespace Bale\GupaPanel\Livewire\Pages\Whitelist\Section;

use Bale\Core\Traits\HasDeleteOption;
use Bale\GupaPanel\Models\PanelWhitelist;
use Livewire\Component;

class WhitelistTable extends Component
{
    use HasDeleteOption;

    protected string $modelClass = PanelWhitelist::class;

    public function render()
    {
        return view('gupa-panel::livewire.pages.whitelist.section.whitelist-table');
    }
}
