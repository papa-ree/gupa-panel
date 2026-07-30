<div>
    <div class="font-mono text-sm">{{ $row->ip }}</div>
</div>
<div>{{ $row->reason ?? '-' }}</div>
<div class="text-sm text-gray-500">{{ $row->created_at->format('d M Y H:i') }}</div>
<div class="text-right">
    <livewire:core-shared-components::item-actions
        :itemId="$row->id"
        editRoute="gupa-panel.whitelist.edit"
    />
</div>
