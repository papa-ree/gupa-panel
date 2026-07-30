<div>
    <div class="font-mono text-sm">{{ $row->ip }}</div>
</div>
<div>{{ $row->reason ?? '-' }}</div>
<div>
    @if($row->is_permanent)
        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">{{ __('Permanent') }}</span>
    @else
        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">{{ __('Temporary') }}</span>
    @endif
</div>
<div class="text-sm text-gray-500">{{ $row->expires_at ? $row->expires_at->format('d M Y H:i') : '-' }}</div>
<div class="text-sm text-gray-500">{{ $row->created_at->format('d M Y H:i') }}</div>
<div class="text-right">
    <livewire:core-shared-components::item-actions
        :itemId="$row->id"
    />
</div>
