<x-core::page-header
    gradient
    :title="__('Blacklist Management')"
    :subtitle="__('Manage blocked IP addresses across all tenants')"
>
    <x-slot name="action">
        <x-core::button link href="{{ route('gupa-panel.blacklist.create') }}" label="Add IP">
            <x-slot name="icon">
                <x-lucide-plus class="w-5 h-5" />
            </x-slot>
        </x-core::button>
    </x-slot>
</x-core::page-header>
