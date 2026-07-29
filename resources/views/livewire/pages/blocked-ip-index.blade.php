<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Blocked IPs</h1>
        <p class="text-gray-600">Manage currently blocked IP addresses</p>
    </div>

    @if (session('message'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded">{{ session('message') }}</div>
    @endif

    <div class="mb-4 flex gap-4">
        <input wire:model.live="search" class="flex-1 border rounded px-3 py-2" placeholder="Search IP address...">
        <select wire:model.live="filterStatus" class="border rounded px-3 py-2">
            <option value="">All Status</option>
            <option value="temporary">Temporary</option>
            <option value="permanent">Permanent</option>
        </select>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Blocked At</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($blockedIps as $blocked)
                    <tr>
                        <td class="px-4 py-2 font-mono text-sm">{{ $blocked->ip }}</td>
                        <td class="px-4 py-2 text-sm text-gray-600">{{ $blocked->reason ?? '-' }}</td>
                        <td class="px-4 py-2">
                            @if($blocked->is_permanent)
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">Permanent</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">Temporary</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $blocked->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-2 text-right">
                            <button wire:click="unblock('{{ $blocked->id }}')" class="text-blue-600 hover:text-blue-800 text-sm">Unblock</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-3 text-sm text-gray-500 text-center">No blocked IPs.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $blockedIps->links() }}</div>
</div>
