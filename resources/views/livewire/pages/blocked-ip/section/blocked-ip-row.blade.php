<tr wire:key="blocked-ip-row-{{ $record->getKey() }}"
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

    {{-- Status --}}
    <td class="px-4 py-3.5 hidden sm:table-cell">
        @if($record->is_permanent)
            <span
                class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-800/40">
                {{ __('Permanent') }}
            </span>
        @else
            <span
                class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400 border border-yellow-100 dark:border-yellow-800/40">
                {{ __('Temporary') }}
            </span>
        @endif
    </td>

    {{-- Expires At --}}
    <td class="px-4 py-3.5 hidden md:table-cell">
        <div class="text-sm text-gray-700 dark:text-gray-300">
            {{ $record->expires_at ?? '—' }}
        </div>
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
            <a href="{{ route('gupa-panel.blocked-ips.logs', $record->ip) }}" wire:navigate.hover
                class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 dark:text-gray-400 dark:hover:text-indigo-400 dark:hover:bg-indigo-900/20 rounded transition-all"
                title="{{ __('View Request Logs') }}">
                <x-lucide-history class="w-4 h-4" />
            </a>
            <livewire:core.shared-components.item-actions :deleteId="$record->id" :navigate="false"
                confirmMessage="{{ __('Yakin ingin menghapus blocked IP ini?') }}"
                wire:key="item-actions-{{ $record->id }}" />
        </div>
    </td>

</tr>