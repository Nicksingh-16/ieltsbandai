<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-surface-50">Institutes</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn-secondary text-sm">← Dashboard</a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[600px] text-sm">
            <thead>
                <tr class="border-b border-surface-800">
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Institute</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Owner</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Plan</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Seats</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Members</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Created</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-800">
                @forelse($institutes as $institute)
                <tr class="hover:bg-surface-900">
                    <td class="px-4 py-3">
                        <div class="font-medium text-surface-100">{{ $institute->name }}</div>
                        <div class="text-surface-500 text-xs">{{ $institute->city }}</div>
                    </td>
                    <td class="px-4 py-3 text-surface-300">{{ optional($institute->owner)->name }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs bg-indigo-500/20 text-indigo-400 px-2 py-0.5 rounded-full capitalize">{{ $institute->plan }}</span>
                    </td>
                    <td class="px-4 py-3 text-surface-300">{{ $institute->seats_used }} / {{ $institute->seat_limit }}</td>
                    <td class="px-4 py-3 text-surface-300">{{ $institute->members_count }}</td>
                    <td class="px-4 py-3 text-surface-500 text-xs">{{ $institute->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.institutes.show', $institute) }}" class="text-xs text-indigo-400 hover:text-indigo-300">View →</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-surface-500">No institutes yet</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">{{ $institutes->links() }}</div>

</div>
</div>
</x-app-layout>
