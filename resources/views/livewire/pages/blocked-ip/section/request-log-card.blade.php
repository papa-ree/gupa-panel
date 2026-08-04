<div class="px-4 py-4" x-data="{ detail: false }">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="font-mono text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $record->created_at }}</p>
            <p class="mt-0.5 font-mono text-xs text-gray-400 dark:text-gray-500 break-all">{{ $record->ip }}</p>
            <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">{{ $record->tenant?->name ?? $record->tenant_id }}</p>
            @if($record->metadata)
                <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500 truncate">{{ $record->metadataSummary() }}</p>
            @endif
        </div>
        <button type="button" @click="detail = true"
            class="shrink-0 p-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 dark:text-gray-400 dark:hover:text-indigo-400 dark:hover:bg-indigo-900/20 rounded transition-all"
            title="{{ __('View Detail') }}">
            <x-lucide-eye class="w-4 h-4" />
        </button>
    </div>

    {{-- Detail Modal --}}
    @include('gupa-panel::livewire.pages.blocked-ip.section.request-log-detail')
</div>
