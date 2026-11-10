<?php

namespace Bale\GupaPanel\Livewire\Pages\BlockedIp\Section;

use Bale\GupaPanel\Jobs\UnblockIpsOnTenants;
use Bale\GupaPanel\Models\PanelBlacklist;
use Bale\GupaPanel\Models\PanelBlockedIp;
use Livewire\Attributes\On;
use Livewire\Component;

class BlockedIpTable extends Component
{
    #[On('unblockBlockedIp')]
    public function unblock(string $id): void
    {
        abort_unless(auth()->user()?->can('gupa-panel.blocked-ip.unblock'), 403);

        $record = PanelBlockedIp::find($id);

        if (! $record) {
            $this->dispatch('toast', message: __('Blocked IP not found'), type: 'error');

            return;
        }

        if (PanelBlacklist::where('ip', $record->ip)->exists()) {
            $this->dispatch(
                'toast',
                message: __('This IP is blacklisted. Remove it from the Blacklist first.'),
                type: 'error',
            );

            return;
        }

        UnblockIpsOnTenants::dispatch([$record->ip]);

        $this->dispatch('toast', message: __('Unblock scheduled. The row disappears once the job finishes.'), type: 'success');
    }

    public function render()
    {
        return view('gupa-panel::livewire.pages.blocked-ip.section.blocked-ip-table');
    }
}
