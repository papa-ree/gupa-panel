<div>
    <div class="font-mono text-sm">{{ $record->ip }}</div>
</div>
<div>{{ $record->reason ?? '-' }}</div>
<div class="text-sm text-gray-500">{{ $record->created_at->format('d M Y H:i') }}</div>
<div class="text-right">
    <livewire:core-shared-components::item-actions
        :itemId="$record->id"
        editRoute="gupa-panel.whitelist.edit"
    />
</div>