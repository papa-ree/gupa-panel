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
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Sync All --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Sync All Data') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Push blacklists, whitelists, and blocked IPs to all tenants') }}</p>
                </div>
                <x-lucide-database class="w-10 h-10 text-indigo-500" />
            </div>
            <x-core::button wire:click="syncAll" variant="primary" class="w-full" type="button" :label="__('Sync All')" />
        </div>

        {{-- Sync Blacklists --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Sync Blacklists') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Push master blacklist to tenant databases') }}</p>
                </div>
                <x-lucide-shield-alert class="w-10 h-10 text-red-500" />
            </div>
            <x-core::button wire:click="syncBlacklists" variant="danger" class="w-full" type="button" :label="__('Sync Blacklists')" />
        </div>

        {{-- Sync Whitelists --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Sync Whitelists') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Push master whitelist to tenant databases') }}</p>
                </div>
                <x-lucide-shield-check class="w-10 h-10 text-green-500" />
            </div>
            <x-core::button wire:click="syncWhitelists" variant="success" class="w-full" type="button" :label="__('Sync Whitelists')" />
        </div>

        {{-- Sync Blocked IPs --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Sync Blocked IPs') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Push blocked IPs to tenant databases') }}</p>
                </div>
                <x-lucide-shield-off class="w-10 h-10 text-orange-500" />
            </div>
            <x-core::button wire:click="syncBlockedIps" variant="danger" class="w-full" type="button" :label="__('Sync Blocked IPs')" />
        </div>

        {{-- Sync Logs (from tenant to landlord) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition md:col-span-2 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Sync Request Logs') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Pull request logs from tenants to landlord database') }}</p>
                </div>
                <x-lucide-activity class="w-10 h-10 text-purple-500" />
            </div>
            <x-core::button wire:click="syncLogs" variant="primary" class="w-full" type="button" :label="__('Sync Logs from Tenants')" />
        </div>

        {{-- Status & Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition md:col-span-2 lg:col-span-2">
            <h3 class="font-semibold text-gray-900 mb-4">{{ __('Sync Information') }}</h3>
            <div class="space-y-3 text-sm text-gray-600">
                <div class="flex items-center justify-between">
                    <span>{{ __('Push Sync (Landlord → Tenant)') }}</span>
                    <span class="font-medium text-indigo-600">{{ __('Manual via buttons above') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>{{ __('Pull Sync (Tenant → Landlord)') }}</span>
                    <span class="font-medium text-purple-600">{{ __('Logs only, via Sync Logs button') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>{{ __('Schedule') }}</span>
                    <span class="font-medium text-gray-500">{{ __('Configure in config/gupa-panel.php') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>{{ __('Jobs') }}</span>
                    <span class="font-medium text-gray-500">{{ __('Queued via Laravel Queue') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>