<div>
    <livewire:core-shared-components::data-table
        model="Bale\GupaPanel\Models\PanelBlockedIp"
        rowView="gupa-panel::livewire.pages.blocked-ip.section.blocked-ip-row"
        cardView="gupa-panel::livewire.pages.blocked-ip.section.blocked-ip-card"
        :columns="[
            ['key' => 'ip', 'label' => __('IP Address'), 'sortable' => true],
            ['key' => 'reason', 'label' => __('Reason'), 'sortable' => true],
            ['key' => 'is_permanent', 'label' => __('Status'), 'sortable' => true],
            ['key' => 'expires_at', 'label' => __('Expires'), 'sortable' => true],
            ['key' => 'created_at', 'label' => __('Blocked At'), 'sortable' => true],
            ['key' => 'actions', 'label' => '', 'sortable' => false],
        ]"
        :searchable="['ip']"
        sortField="created_at"
        sortDirection="desc"
        :perPage="20"
    />
</div>
