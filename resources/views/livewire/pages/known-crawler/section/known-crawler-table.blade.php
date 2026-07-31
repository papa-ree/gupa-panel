<div>
    <livewire:core-shared-components::data-table
        model="Bale\GupaPanel\Models\KnownCrawler"
        rowView="gupa-panel::livewire.pages.known-crawler.section.known-crawler-row"
        :columns="[
            ['key' => 'name', 'label' => __('Name'), 'sortable' => true],
            ['key' => 'provider', 'label' => __('Provider'), 'sortable' => true],
            ['key' => 'user_agent_pattern', 'label' => __('UA Pattern'), 'sortable' => true],
            ['key' => 'is_active', 'label' => __('Status'), 'sortable' => true],
            ['key' => 'created_at', 'label' => __('Created'), 'sortable' => true],
            ['key' => 'actions', 'label' => '', 'sortable' => false],
        ]"
        :searchable="['name', 'provider', 'user_agent_pattern']"
        sortField="name"
        sortDirection="asc"
        :perPage="20"
    />
</div>