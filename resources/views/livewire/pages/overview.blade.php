<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Gupa Panel Overview</h1>
        <p class="text-gray-600">Real-time security monitoring dashboard</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Total Blocked</div>
            <div class="text-2xl font-bold">{{ $stats['total_blocked'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Temporary</div>
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['temporary_blocked'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Permanent</div>
            <div class="text-2xl font-bold text-red-600">{{ $stats['permanent_blocked'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Whitelisted</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['total_whitelisted'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Blacklisted</div>
            <div class="text-2xl font-bold text-red-600">{{ $stats['total_blacklisted'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Total Logs</div>
            <div class="text-2xl font-bold">{{ $stats['total_logs'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Logs Today</div>
            <div class="text-2xl font-bold">{{ $stats['logs_today'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-semibold">Recent Logs</div>
            <div class="divide-y">
                @forelse($recentLogs as $log)
                    <div class="px-4 py-2 text-sm">
                        <span class="font-mono text-xs">{{ $log['ip'] }}</span>
                        <span class="text-gray-500">— {{ \Illuminate\Support\Str::limit($log['reason'] ?? 'N/A', 60) }}</span>
                        <span class="text-gray-400 text-xs float-right">{{ $log['created_at'] }}</span>
                    </div>
                @empty
                    <div class="px-4 py-3 text-sm text-gray-500">No logs yet.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-semibold">Potential False Positives</div>
            <div class="divide-y">
                @forelse($recentFalsePositives as $fp)
                    <div class="px-4 py-2 text-sm">
                        <span class="font-mono text-xs">{{ $fp['blocked_ip']->ip }}</span>
                        <span class="text-green-600 text-xs ml-1">({{ $fp['analysis']['matched_crawler'] ?? 'Unknown' }})</span>
                        <span class="text-gray-400 text-xs float-right">{{ $fp['analysis']['confidence_score'] }}%</span>
                    </div>
                @empty
                    <div class="px-4 py-3 text-sm text-gray-500">No false positives detected.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
