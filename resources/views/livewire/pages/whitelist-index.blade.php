<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Whitelist Management</h1>
        <button wire:click="$toggle('showForm')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            {{ $showForm ? 'Cancel' : 'Add IP' }}
        </button>
    </div>

    @if (session('message'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded">{{ session('message') }}</div>
    @endif

    @if ($showForm)
        <form wire:submit="add" class="mb-6 bg-white rounded-lg shadow p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">IP Address / CIDR / Wildcard</label>
                    <input wire:model="ip" class="w-full border rounded px-3 py-2" placeholder="192.168.1.0/24">
                    @error('ip') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Reason</label>
                    <input wire:model="reason" class="w-full border rounded px-3 py-2" placeholder="Optional reason">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Add to Whitelist</button>
                </div>
            </div>
        </form>
    @endif

    <div class="mb-4">
        <input wire:model.live="search" class="w-full border rounded px-3 py-2" placeholder="Search IP address...">
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($whitelists as $whitelist)
                    <tr>
                        <td class="px-4 py-2 font-mono text-sm">{{ $whitelist->ip }}</td>
                        <td class="px-4 py-2 text-sm text-gray-600">{{ $whitelist->reason ?? '-' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $whitelist->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-2 text-right">
                            <button wire:click="delete('{{ $whitelist->id }}')" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-3 text-sm text-gray-500 text-center">No whitelisted IPs.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $whitelists->links() }}</div>
</div>
