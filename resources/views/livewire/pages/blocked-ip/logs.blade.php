<div class="space-y-6">
    <style>[x-cloak]{display:none!important}</style>

    <x-core::page-header
        gradient
        :title="__('Request Logs')"
        :subtitle="__('Request logs recorded for IP :ip across all tenants.', ['ip' => $blocked->ip])"
    >
        <x-slot:action>
            <a href="{{ route('gupa-panel.blocked-ips') }}" wire:navigate.hover
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-white/15 hover:bg-white/25 border border-white/20 rounded-xl transition-all">
                <x-lucide-arrow-left class="w-4 h-4" />
                {{ __('Back') }}
            </a>
        </x-slot:action>
    </x-core::page-header>

    {{-- Blocked IP summary --}}
    <div class="relative overflow-hidden bg-white border border-gray-200 shadow-sm dark:bg-gray-900 rounded-2xl dark:border-gray-700/60">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 size-11 rounded-xl bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400 flex items-center justify-center">
                        <x-lucide-box class="w-5 h-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">{{ __('Blocked IP') }}</p>
                        <p class="mt-1 font-mono text-lg font-semibold text-gray-900 dark:text-white break-all">{{ $blocked->ip }}</p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $blocked->reason ?? __('No reason provided') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @if($blocked->is_permanent)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border border-red-100 dark:border-red-800/40">
                            {{ __('Permanent') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400 border border-yellow-100 dark:border-yellow-800/40">
                            {{ __('Temporary') }}
                        </span>
                    @endif
                </div>
            </div>

            <dl class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3 border-t border-gray-100 dark:border-gray-700/60 pt-5">
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ __('Expires At') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $blocked->expires_at ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ __('Blocked At') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $blocked->created_at }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">{{ __('Total Request Logs') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ number_format(\Bale\GupaPanel\Models\PanelRequestLog::where('ip', $blocked->ip)->count()) }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Request logs datatable --}}
    <livewire:core-shared-components::data-table
        model="Bale\GupaPanel\Models\PanelRequestLog"
        rowView="gupa-panel::livewire.pages.blocked-ip.section.request-log-row"
        cardView="gupa-panel::livewire.pages.blocked-ip.section.request-log-card"
        :with="['tenant']"
        :columns="[
            ['key' => 'created_at', 'label' => __('Timestamp'), 'sortable' => true],
            ['key' => 'tenant_id', 'label' => __('Tenant'), 'sortable' => true, 'hidden' => 'sm'],
            ['key' => 'tenant_log_id', 'label' => __('Log ID'), 'sortable' => true, 'hidden' => 'md'],
            ['key' => 'metadata', 'label' => __('Metadata'), 'sortable' => false, 'hidden' => 'lg'],
            ['key' => 'actions', 'label' => '', 'sortable' => false],
        ]"
        :constraints="['ip' => $ip]"
        :searchable="['tenant_log_id', 'ip']"
        sortField="created_at"
        sortDirection="desc"
        :perPage="20"
    />
</div>
