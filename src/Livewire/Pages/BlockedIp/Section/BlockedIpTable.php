<?php

namespace Bale\GupaPanel\Livewire\Pages\BlockedIp\Section;

use Bale\Core\Traits\HasDeleteOption;
use Livewire\Component;

class BlockedIpTable extends Component
{
    use HasDeleteOption;

    protected string $modelClass = \Bale\GupaPanel\Models\PanelBlockedIp::class;

    public function render()
    {
        return view('gupa-panel::livewire.pages.blocked-ip.section.blocked-ip-table');
    }
}
