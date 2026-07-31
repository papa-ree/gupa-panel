<x-core::page-header
    gradient
    :title="__('Known Crawlers')"
    :subtitle="__('Manage verified crawler IP ranges for false positive detection')"
>
    <x-slot name="action">
        <x-core::button link href="{{ route('gupa-panel.known-crawler.create') }}" label="{{ __('Add Crawler') }}">
            <x-slot name="icon">
                <x-lucide-plus class="w-5 h-5" />
            </x-slot>
        </x-core::button>
    </x-slot>
</x-core::page-header>