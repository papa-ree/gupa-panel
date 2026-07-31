<tr wire:key="known-crawler-row-{{ $record->getKey() }}"
    class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition-colors duration-150">

    {{-- Name --}}
    <td class="px-4 py-3.5 w-full max-w-0 sm:max-w-none sm:w-auto">
        <div class="font-medium text-sm text-gray-900 dark:text-gray-100">
            {{ $record->name }}
        </div>
    </td>

    {{-- Provider --}}
    <td class="px-4 py-3.5 hidden lg:table-cell">
        <div class="text-sm text-gray-700 dark:text-gray-300 truncate max-w-xs">
            {{ $record->provider }}
        </div>
    </td>

    {{-- UA Pattern --}}
    <td class="px-4 py-3.5 hidden md:table-cell">
        <div class="text-sm text-gray-700 dark:text-gray-300 font-mono truncate max-w-xs">
            {{ $record->user_agent_pattern }}
        </div>
    </td>

    {{-- Status --}}
    <td class="px-4 py-3.5 hidden sm:table-cell">
        @if($record->is_active)
            <span
                class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 border border-green-100 dark:border-green-800/40">
                {{ __('Active') }}
            </span>
        @else
            <span
                class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-50 text-gray-700 dark:bg-gray-900/20 dark:text-gray-400 border border-gray-100 dark:border-gray-800/40">
                {{ __('Inactive') }}
            </span>
        @endif
    </td>

    {{-- Created At --}}
    <td class="px-4 py-3.5 hidden lg:table-cell">
        <div class="text-sm text-gray-700 dark:text-gray-300">
            {{ $record->created_at }}
        </div>
    </td>

    {{-- Actions --}}
    <td class="px-4 py-3.5 whitespace-nowrap w-px">
        <div class="flex items-center gap-1">
            <button type="button" wire:click="$dispatch('addToWhitelist', { id: '{{ $record->id }}' })"
                wire:confirm="{{ __('Tambahkan semua IP dari crawler ini ke whitelist?') }}"
                title="{{ __('Add to whitelist') }}"
                class="inline-flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:text-green-600 hover:bg-green-50 dark:hover:text-green-400 dark:hover:bg-green-900/20 transition-colors duration-150">
                <x-lucide-plus class="w-4 h-4" />
            </button>
            <livewire:core.shared-components.item-actions :editUrl="route('gupa-panel.known-crawler.edit', $record->id)"
                :deleteId="$record->id" :navigate="false"
                confirmMessage="{{ __('Yakin ingin menghapus known crawler ini?') }}"
                wire:key="item-actions-{{ $record->id }}" />
        </div>
    </td>

</tr>