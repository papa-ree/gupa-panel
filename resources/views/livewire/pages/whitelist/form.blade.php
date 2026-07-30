<div>
    <x-core::page-header
        :title="$whitelistId ? __('Edit Whitelist Entry') : __('Add Whitelist Entry')"
        :subtitle="__('Manage trusted IP addresses')"
    />

    <form wire:submit="save" class="max-w-2xl mx-auto mt-6 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('IP Address / CIDR / Wildcard') }}</label>
                <input wire:model="ip" class="w-full border rounded-lg px-3 py-2" placeholder="192.168.1.0/24">
                @error('ip') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Reason') }}</label>
                <input wire:model="reason" class="w-full border rounded-lg px-3 py-2" placeholder="Optional reason">
            </div>
            <div class="flex items-center gap-3 pt-2">
                <x-core::button type="submit" variant="success" :label="$whitelistId ? __('Update') : __('Add to Whitelist')" />
                <x-core::button link href="{{ route('gupa-panel.whitelist') }}" variant="secondary" :label="__('Cancel')" />
            </div>
        </div>
    </form>
</div>
