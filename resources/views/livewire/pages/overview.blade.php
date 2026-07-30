<div>
    {{-- Page Header --}}
    <x-core::page-header gradient :title="__('Gupa Security Dashboard')" :subtitle="__('Real-time threat monitoring, IP restrictions, and false positive analysis.')" />

    {{-- Compact Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Total Blocked') }}</p>
                    <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $stats['total_blocked'] }}</p>
                </div>
                <x-lucide-shield-off class="w-5 h-5 text-red-500" />
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-gray-500 pt-2 border-t border-gray-50">
                <span class="text-yellow-600 font-medium">{{ __('Temp') }}: {{ $stats['temporary_blocked'] }}</span>
                <span class="text-red-600 font-medium">{{ __('Perm') }}: {{ $stats['permanent_blocked'] }}</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Whitelisted') }}</p>
                    <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $stats['total_whitelisted'] }}</p>
                </div>
                <x-lucide-shield-check class="w-5 h-5 text-green-500" />
            </div>
            <div class="mt-2 text-xs text-gray-500 pt-2 border-t border-gray-50">
                {{ __('Trusted entries') }}
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Blacklisted') }}</p>
                    <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $stats['total_blacklisted'] }}</p>
                </div>
                <x-lucide-shield-alert class="w-5 h-5 text-blue-500" />
            </div>
            <div class="mt-2 text-xs text-gray-500 pt-2 border-t border-gray-50">
                {{ __('Master restriction list') }}
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Logs Today') }}</p>
                    <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ $stats['logs_today'] }}</p>
                </div>
                <x-lucide-activity class="w-5 h-5 text-purple-500" />
            </div>
            <div class="mt-2 text-xs text-gray-500 pt-2 border-t border-gray-50">
                {{ __('Total') }}: {{ $stats['total_logs'] }}
            </div>
        </div>
    </div>

    {{-- Detailed Section Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Logs --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('Recent Request Logs') }}</h3>
                    <p class="text-xs text-gray-500">{{ __('Latest traffic captured across tenant nodes') }}</p>
                </div>
                <span class="px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">{{ __('Live Feed') }}</span>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentLogs as $log)
                    <div class="px-5 py-3.5 hover:bg-gray-50/80 transition flex items-center justify-between text-sm">
                        <div class="flex items-center space-x-3">
                            <span class="font-mono text-xs font-semibold px-2 py-1 bg-gray-100 text-gray-700 rounded">
                                {{ $log['ip'] }}
                            </span>
                            <span class="text-gray-600 truncate max-w-xs">
                                {{ data_get($log['metadata'], 'user_agent', __('Standard Request')) }}
                            </span>
                        </div>
                        <span class="text-xs text-gray-400 whitespace-nowrap ml-2">
                            {{ \Carbon\Carbon::parse($log['created_at'])->diffForHumans() }}
                        </span>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400 text-sm">
                        {{ __('No request logs recorded yet.') }}
                    </div>
                @endforelse
            </div>
        </div>

        {{-- False Positives --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ __('False Positive Candidates') }}</h3>
                    <p class="text-xs text-gray-500">{{ __('Blocked IPs matching known crawlers or safe signals') }}</p>
                </div>
                <a href="{{ route('gupa-panel.review-false-positive') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                    {{ __('View All') }} &rarr;
                </a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentFalsePositives as $fp)
                    <div class="px-5 py-3.5 hover:bg-gray-50/80 transition flex items-center justify-between text-sm">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-mono text-xs font-bold text-gray-900">{{ $fp['blocked_ip']->ip }}</span>
                                <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-50 text-emerald-700 font-medium">
                                    {{ $fp['analysis']['matched_crawler'] ?? __('Bot-like') }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ __('Confidence Score') }}: <span class="font-semibold text-gray-700">{{ $fp['analysis']['confidence_score'] }}%</span>
                            </p>
                        </div>
                        <a href="{{ route('gupa-panel.review-false-positive') }}" class="px-3 py-1.5 text-xs font-medium bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">
                            {{ __('Review') }}
                        </a>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400 text-sm">
                        {{ __('No false positive candidates found.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
