<div class="px-4 py-4">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 flex-wrap">
                <p class="font-mono text-sm font-semibold text-gray-900 dark:text-gray-100 break-all">{{ $record->ip }}</p>
                @if($record->is_permanent)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-800/40">
                        {{ __('Permanent') }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400 border border-yellow-100 dark:border-yellow-800/40">
                        {{ __('Temporary') }}
                    </span>
                @endif
            </div>
            @if($record->reason)
                <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $record->reason }}</p>
            @endif
        </div>
        <div class="shrink-0 flex items-center gap-1">
            <a href="{{ route('gupa-panel.blocked-ips.logs', $record->ip) }}" wire:navigate.hover
                class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 dark:text-gray-400 dark:hover:text-indigo-400 dark:hover:bg-indigo-900/20 rounded transition-all"
                title="{{ __('View Request Logs') }}">
                <x-lucide-history class="w-4 h-4" />
            </a>
            <livewire:core.shared-components.item-actions :deleteId="$record->id" :navigate="false"
                confirmMessage="{{ __('Yakin ingin menghapus blocked IP ini?') }}"
                wire:key="item-actions-card-{{ $record->id }}" />
        </div>
    </div>

    <dl class="mt-3 grid grid-cols-2 gap-3 border-t border-gray-100 dark:border-gray-700/60 pt-3">
        <div>
            <dt class="text-[11px] font-medium text-gray-400 dark:text-gray-500">{{ __('Expires') }}</dt>
            <dd class="mt-0.5 text-sm text-gray-700 dark:text-gray-300 break-words">{{ $record->expires_at ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-[11px] font-medium text-gray-400 dark:text-gray-500">{{ __('Blocked At') }}</dt>
            <dd class="mt-0.5 text-sm text-gray-700 dark:text-gray-300">{{ $record->created_at }}</dd>
        </div>
    </dl>
</div>
