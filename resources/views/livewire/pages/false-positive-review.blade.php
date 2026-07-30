<div>
    <x-core::page-header
        gradient
        :title="__('False Positive Review')"
        :subtitle="__('Review blocked IPs that may be legitimate crawlers or users')"
    />

    @if (session('message'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded">{{ session('message') }}</div>
    @endif

    @if(count($candidates) > 0)
        <div class="mb-4 flex gap-2">
            <x-core::button wire:click="whitelistSelected" variant="success" :disabled="empty($selected)" :label="__('Whitelist & Unblock Selected')" />
            <x-core::button wire:click="unblockOnly" variant="primary" :disabled="empty($selected)" :label="__('Unblock Only')" />
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 w-10"><input type="checkbox" wire:model="selected"></th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('IP Address') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Matched Crawler') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Confidence') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Reasons') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Blocked At') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($candidates as $candidate)
                    <tr>
                        <td class="px-4 py-2">
                            <input type="checkbox" wire:model="selected" value="{{ $candidate['id'] }}">
                        </td>
                        <td class="px-4 py-2 font-mono text-sm">{{ $candidate['ip'] }}</td>
                        <td class="px-4 py-2 text-sm">
                            @if($candidate['analysis']['matched_crawler'])
                                <span class="text-green-700">{{ $candidate['analysis']['matched_crawler'] }}</span>
                                <span class="text-gray-400 text-xs">({{ $candidate['analysis']['provider'] }})</span>
                            @else
                                <span class="text-gray-400">{{ __('Unknown') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-1">
                                <div class="w-16 bg-gray-200 rounded h-2">
                                    <div class="bg-green-500 rounded h-2" style="width: {{ $candidate['analysis']['confidence_score'] }}%"></div>
                                </div>
                                <span class="text-xs">{{ $candidate['analysis']['confidence_score'] }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-600">
                            {{ implode('; ', $candidate['analysis']['reasons'] ?? []) }}
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $candidate['created_at'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-3 text-sm text-gray-500 text-center">{{ __('No false positive candidates found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>