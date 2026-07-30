<div>
    <div class="font-mono text-sm">{{ $record->ip }}</div>
</div>
<div>{{ $record->reason ?? '-' }}</div>
<div>
    @if($record->is_permanent)
        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">{{ __('Permanent') }}</span>
    @else
        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">{{ __('Temporary') }}</span>
    @endif
</div>
<div class="text-sm text-gray-500">{{ $record->expires_at ?? '-' }}</div>
<div class="text-sm text-gray-500">{{ $record->created_at }}</div>
<div class="text-right">
    <livewire:core-shared-components::item-actions
        :itemId="$record->id"
    />
</div>