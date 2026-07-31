<div>
    <x-core::page-header
        :title="$crawlerId ? __('Edit Known Crawler') : __('Add Known Crawler')"
        :subtitle="__('Manage verified crawler IP ranges for false positive detection')"
    />

    <form wire:submit="save" class="max-w-2xl mx-auto mt-6 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Name') }}</label>
                <input wire:model="name" class="w-full border rounded-lg px-3 py-2" placeholder="Googlebot">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Provider') }}</label>
                <input wire:model="provider" class="w-full border rounded-lg px-3 py-2" placeholder="Google">
                @error('provider') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ __('User Agent Pattern') }}</label>
                <input wire:model="user_agent_pattern" class="w-full border rounded-lg px-3 py-2" placeholder="Googlebot">
                <p class="text-xs text-gray-500 mt-1">{{ __('Substring to match in User-Agent header') }}</p>
                @error('user_agent_pattern') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">{{ __('Verified IP Ranges (CIDR)') }}</label>
                <div class="space-y-2" id="ip-ranges">
                    @foreach($verified_ip_ranges as $index => $range)
                        <div class="flex items-center gap-2">
                            <input wire:model="verified_ip_ranges.{{ $index }}" class="flex-1 border rounded-lg px-3 py-2" placeholder="66.249.64.0/19">
                            <button type="button" wire:click="removeIpRange({{ $index }})" class="px-3 py-2 text-red-600 hover:text-red-800">
                                {{ __('Remove') }}
                            </button>
                        </div>
                    @endforeach
                </div>
                <button type="button" wire:click="addIpRange" class="mt-2 px-3 py-1.5 text-sm text-indigo-600 hover:text-indigo-800 border border-indigo-300 rounded-lg">
                    + {{ __('Add IP Range') }}
                </button>
                <p class="text-xs text-gray-500 mt-1">{{ __('Leave empty to match by User-Agent only (lower confidence)') }}</p>
                @error('verified_ip_ranges.*') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm">{{ __('Active') }}</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    {{ $crawlerId ? __('Update') : __('Add Crawler') }}
                </button>
                <a href="{{ route('gupa-panel.known-crawler') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    {{ __('Cancel') }}
                </a>
            </div>
        </div>
    </form>
</div>