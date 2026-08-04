<div>
    <livewire:core-shared-components::data-table
        model="Bale\GupaPanel\Models\PanelBlacklist"
        rowView="gupa-panel::livewire.pages.blacklist.section.blacklist-row"
        cardView="gupa-panel::livewire.pages.blacklist.section.blacklist-card"
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
