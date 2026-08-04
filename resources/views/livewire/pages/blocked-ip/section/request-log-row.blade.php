<tr wire:key="request-log-row-{{ $record->getKey() }}"
    x-data="{ detail: false }"
    class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition-colors duration-150">

    {{-- Timestamp (primary, always visible) --}}
    <td class="px-4 py-3.5 w-full max-w-0 sm:max-w-none sm:w-auto">
        <div class="font-mono text-sm text-gray-900 dark:text-gray-100">
            {{ $record->created_at }}
        </div>
        <div class="mt-1 text-xs font-mono text-gray-400 dark:text-gray-500 block sm:hidden">
            {{ $record->ip }}
        </div>
    </td>

    {{-- Tenant --}}
    <td class="px-4 py-3.5 hidden sm:table-cell">
        <div class="text-sm text-gray-700 dark:text-gray-300 truncate max-w-xs">
            {{ $record->tenant?->name ?? $record->tenant_id }}
        </div>
    </td>

    {{-- Tenant Log ID --}}
    <td class="px-4 py-3.5 hidden md:table-cell">
        <div class="text-xs font-mono text-gray-500 dark:text-gray-400 truncate max-w-[10rem]">
            {{ $record->tenant_log_id ?? '—' }}
        </div>
    </td>

    {{-- Metadata Summary --}}
    <td class="px-4 py-3.5 hidden lg:table-cell">
        <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">
            {{ $record->metadataSummary() }}
        </div>
    </td>

    {{-- Actions --}}
    <td class="px-4 py-3.5 whitespace-nowrap w-px">
        <button type="button" @click="detail = true"
            class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 dark:text-gray-400 dark:hover:text-indigo-400 dark:hover:bg-indigo-900/20 rounded transition-all"
            title="{{ __('View Detail') }}">
            <x-lucide-eye class="w-4 h-4" />
        </button>
    </td>

    {{-- Detail Modal --}}
    @include('gupa-panel::livewire.pages.blocked-ip.section.request-log-detail')

</tr>
