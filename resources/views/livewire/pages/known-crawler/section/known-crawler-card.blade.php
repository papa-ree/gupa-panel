<div class="px-4 py-4">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $record->name }}</p>
                @if($record->is_active)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 border border-green-100 dark:border-green-800/40">
                        {{ __('Active') }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-50 text-gray-700 dark:bg-gray-900/20 dark:text-gray-400 border border-gray-100 dark:border-gray-800/40">
                        {{ __('Inactive') }}
                    </span>
                @endif
            </div>
            <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">{{ $record->provider }}</p>
            <p class="mt-1 font-mono text-xs text-gray-500 dark:text-gray-400 break-all line-clamp-2">{{ $record->user_agent_pattern }}</p>
        </div>
        <div class="shrink-0 flex items-center gap-1">
            <button type="button" wire:click="$dispatch('addToWhitelist', { id: '{{ $record->id }}' })"
                wire:confirm="{{ __('Tambahkan semua IP dari crawler ini ke whitelist?') }}"
                title="{{ __('Add to whitelist') }}"
                class="inline-flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:text-green-600 hover:bg-green-50 dark:hover:text-green-400 dark:hover:bg-green-900/20 transition-colors duration-150">
                <x-lucide-plus class="w-4 h-4" />
            </button>
            <livewire:core.shared-components.item-actions :editUrl="route('gupa-panel.known-crawler.edit', $record->id)"
                :deleteId="$record->id" :navigate="false"
                confirmMessage="{{ __('Yakin ingin menghapus known crawler ini?') }}"
                wire:key="item-actions-card-{{ $record->id }}" />
        </div>
    </div>

    <dl class="mt-3 border-t border-gray-100 dark:border-gray-700/60 pt-3">
        <div>
            <dt class="text-[11px] font-medium text-gray-400 dark:text-gray-500">{{ __('Created') }}</dt>
            <dd class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $record->created_at }}</dd>
        </div>
    </dl>
</div>
