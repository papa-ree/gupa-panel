<div>
    <x-core::page-header
        gradient
        :title="__('Sync Management')"
        :subtitle="__('Manage data synchronization between landlord and tenant databases')"
    />

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Blacklists') }}</p>
            <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $syncStats['blacklists'] ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Whitelists') }}</p>
            <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $syncStats['whitelists'] ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Blocked IPs') }}</p>
            <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $syncStats['blocked_ips'] ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Request Logs') }}</p>
            <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $syncStats['logs'] ?? 0 }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Last Sync') }}</p>
            <p class="text-sm font-medium text-gray-900 mt-1">
                {{ $syncStats['last_sync'] ? \Carbon\Carbon::parse($syncStats['last_sync'])->diffForHumans() : __('Never') }}
            </p>
        </div>
    </div>

    {{-- Action Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Sync Panel Data (blocked_ip + blacklist + whitelist) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Sync Panel Data') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Bidirectional sync of blacklists, whitelists, and blocked IPs with all tenants') }}</p>
                </div>
                <x-lucide-database class="w-10 h-10 text-indigo-500" />
            </div>
            <x-core::button wire:click="syncPanelData" variant="primary" class="w-full" type="button" :label="__('Sync Panel Data to Tenants')" />
        </div>

        {{-- Sync Logs (from tenant to landlord) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Sync Request Logs') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Pull request logs from tenants to landlord database (deduplicated by tenant_log_id)') }}</p>
                </div>
                <x-lucide-activity class="w-10 h-10 text-purple-500" />
            </div>
            <x-core::button wire:click="syncLogs" variant="primary" class="w-full" type="button" :label="__('Sync Logs from Tenants')" />
        </div>

        {{-- Info & Sync All --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition md:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Sync All Data') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Run both Panel Data sync and Request Log sync sequentially via queue') }}</p>
                </div>
                <x-lucide-refresh-cw class="w-10 h-10 text-emerald-500" />
            </div>
            <x-core::button wire:click="syncAll" variant="success" class="w-full" type="button" :label="__('Sync All')" />
        </div>
    </div>
</div>
