<template x-teleport="body">
    <div x-show="detail" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-[70] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="detail = false"></div>
        <div class="relative w-full max-w-2xl max-h-[85vh] overflow-y-auto bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700">
            <div class="sticky top-0 flex items-center justify-between px-5 py-4 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-700/60">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="shrink-0 size-9 rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 flex items-center justify-center">
                        <x-lucide-activity class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ __('Request Log Detail') }}</h3>
                        <p class="text-xs font-mono text-gray-500 dark:text-gray-400 truncate">{{ $record->ip }}</p>
                    </div>
                </div>
                <button type="button" @click="detail = false"
                    class="shrink-0 p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-800 rounded-lg transition-colors"
                    title="{{ __('Close') }}">
                    <x-lucide-x class="w-4 h-4" />
                </button>
            </div>

            <div class="p-5 space-y-5">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ __('Timestamp') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $record->created_at }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ __('Tenant') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100 break-all">{{ $record->tenant?->name ?? $record->tenant_id }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ __('Tenant Log ID') }}</dt>
                        <dd class="mt-1 text-sm font-mono text-gray-900 dark:text-gray-100 break-all">{{ $record->tenant_log_id ?? '—' }}</dd>
                    </div>
                </dl>

                <div>
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ __('Metadata') }}</p>
                    @if($record->metadata)
                        <div class="mt-2">
                            <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                @foreach($record->metadata as $key => $value)
                                    <div class="bg-gray-50 dark:bg-gray-800/60 rounded-lg px-3 py-2 border border-gray-100 dark:border-gray-700/60">
                                        <dt class="text-[11px] font-medium text-gray-400 dark:text-gray-500 break-all">{{ $key }}</dt>
                                        <dd class="mt-0.5 text-xs text-gray-700 dark:text-gray-300 break-all">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @else
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">—</p>
                    @endif
                </div>

                <div>
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ __('Raw Metadata') }}</p>
                    <pre class="mt-2 overflow-x-auto rounded-lg bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700/60 px-3 py-2 text-xs leading-relaxed text-gray-700 dark:text-gray-300">{{ $record->metadataSummary(4000) }}</pre>
                </div>
            </div>
        </div>
    </div>
</template>
