<x-core::page-header
    gradient
    :title="__('Whitelist Management')"
    :subtitle="__('Manage trusted IP addresses that bypass restrictions')"
>
    <x-slot name="action">
        <x-core::button link href="{{ route('gupa-panel.whitelist.create') }}" label="Add IP">
            <x-slot name="icon">
                <x-lucide-plus class="w-5 h-5" />
            </x-slot>
        </x-core::button>
    </x-slot>
</x-core::page-header>
