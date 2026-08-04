<?php

namespace Bale\GupaPanel\Livewire\Pages\BlockedIp\Section;

use Bale\Core\Traits\HasDeleteOption;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Livewire\Component;

class BlockedIpTable extends Component
{
    use HasDeleteOption;

    protected string $modelClass = PanelBlockedIp::class;

    public function render()
    {
        return view('gupa-panel::livewire.pages.blocked-ip.section.blocked-ip-table');
    }
}
