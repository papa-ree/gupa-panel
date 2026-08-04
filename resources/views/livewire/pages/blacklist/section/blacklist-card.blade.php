<div class="px-4 py-4">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <p class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">{{ __('IP Address') }}</p>
            <p class="mt-0.5 font-mono text-sm font-semibold text-gray-900 dark:text-gray-100 break-all">{{ $record->ip }}</p>
            @if($record->reason)
                <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $record->reason }}</p>
            @endif
            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ $record->created_at }}</p>
        </div>
        <div class="shrink-0">
            <livewire:core.shared-components.item-actions :editUrl="route('gupa-panel.blacklist.edit', $record->id)"
                :deleteId="$record->id" :navigate="false" confirmMessage="{{ __('Yakin ingin menghapus blacklist ini?') }}"
                wire:key="item-actions-card-{{ $record->id }}" />
        </div>
    </div>
</div>
