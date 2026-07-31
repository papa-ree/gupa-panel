<tr wire:key="blacklist-row-{{ $record->getKey() }}"
    class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition-colors duration-150">

    {{-- IP Address --}}
    <td class="px-4 py-3.5 w-full max-w-0 sm:max-w-none sm:w-auto">
        <div class="font-mono text-sm text-gray-900 dark:text-gray-100">
            {{ $record->ip }}
        </div>
    </td>

    {{-- Reason --}}
    <td class="px-4 py-3.5 hidden lg:table-cell">
        <div class="text-sm text-gray-700 dark:text-gray-300 truncate max-w-xs">
            {{ $record->reason ?? '—' }}
        </div>
    </td>

    {{-- Created At --}}
    <td class="px-4 py-3.5 hidden md:table-cell">
        <div class="text-sm text-gray-700 dark:text-gray-300">
            {{ $record->created_at }}
        </div>
    </td>

    {{-- Actions --}}
    <td class="px-4 py-3.5 whitespace-nowrap w-px">
        <livewire:core.shared-components.item-actions :editUrl="route('gupa-panel.blacklist.edit', $record->id)"
            :deleteId="$record->id" :navigate="false" confirmMessage="{{ __('Yakin ingin menghapus blacklist ini?') }}"
            wire:key="item-actions-{{ $record->id }}" />
    </td>

</tr>