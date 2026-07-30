<div>
    <livewire:core-shared-components::data-table
        model="Bale\GupaPanel\Models\PanelWhitelist"
        rowView="gupa-panel::livewire.pages.whitelist.section.whitelist-row"
        :columns="[
            ['key' => 'ip', 'label' => __('IP Address'), 'sortable' => true],
            ['key' => 'reason', 'label' => __('Reason'), 'sortable' => true],
            ['key' => 'created_at', 'label' => __('Created'), 'sortable' => true],
            ['key' => 'actions', 'label' => '', 'sortable' => false],
        ]"
        :searchable="['ip']"
        sortField="created_at"
        sortDirection="desc"
        :perPage="20"
    />
</div>
