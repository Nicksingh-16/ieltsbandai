<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('admin.institutes') }}" class="text-surface-500 hover:text-surface-300 text-sm">← Institutes</a>
            <h1 class="text-2xl font-bold text-surface-50 mt-2">{{ $institute->name }}</h1>
            <div class="flex items-center gap-3 mt-1 text-sm">
                <span class="capitalize bg-indigo-500/20 text-indigo-400 px-2 py-0.5 rounded-full text-xs">{{ $institute->plan }}</span>
                @if($institute->is_active)
                    <span class="text-emerald-400 text-xs">● Active</span>
                @else
                    <span class="text-red-400 text-xs">● Suspended</span>
                @endif
                <span class="text-surface-500 text-xs">{{ $institute->city }}</span>
            </div>
        </div>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('admin.institutes.toggle', $institute) }}">
                @csrf
                <button class="text-sm {{ $institute->is_active ? 'btn-secondary text-red-400' : 'btn-primary' }}">
                    {{ $institute->is_active ? 'Suspend' : 'Activate' }}
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-4 py-3 text-emerald-400 text-sm mb-6">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Stats --}}
        <div class="card p-5 space-y-3">
            <h3 class="text-sm font-semibold text-surface-300 mb-3">Overview</h3>
            <div class="flex justify-between text-sm">
                <span class="text-surface-400">Owner</span>
                <span class="text-surface-200">{{ optional($institute->owner)->name }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-surface-400">Email</span>
                <span class="text-surface-200 text-xs">{{ $institute->contact_email }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-surface-400">Phone</span>
                <span class="text-surface-200">{{ $institute->phone ?? '—' }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-surface-400">GST</span>
                <span class="text-surface-200">{{ $institute->gst_number ?? '—' }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-surface-400">Seats</span>
                <span class="text-surface-200">{{ $institute->seats_used }} / {{ $institute->seat_limit }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-surface-400">Total Tests</span>
                <span class="text-surface-200">{{ $totalTests }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-surface-400">Batches</span>
                <span class="text-surface-200">{{ $institute->batches->count() }}</span>
            </div>
        </div>

        {{-- Update Plan --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-surface-300 mb-4">Update Plan</h3>
            <form method="POST" action="{{ route('admin.institutes.plan', $institute) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-surface-400 mb-1">Plan</label>
                    <select name="plan"
                        class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2 text-surface-200 text-sm focus:outline-none focus:border-indigo-500">
                        @foreach(['free','basic','pro','enterprise'] as $p)
                            <option value="{{ $p }}" @selected($institute->plan == $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-surface-400 mb-1">Seat Limit</label>
                    <input type="number" name="seat_limit" value="{{ $institute->seat_limit }}" min="1"
                        class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2 text-surface-200 text-sm focus:outline-none focus:border-indigo-500">
                </div>
                <button type="submit" class="btn-primary text-sm w-full">Update Plan</button>
            </form>
        </div>

        {{-- Question Sets --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-surface-300 mb-3">Question Sets ({{ $questionSets->count() }})</h3>
            @forelse($questionSets as $qs)
            <div class="flex items-center justify-between py-2 border-b border-surface-800 last:border-0">
                <div>
                    <div class="text-surface-200 text-sm">{{ $qs->name }}</div>
                    <div class="text-surface-500 text-xs">{{ $qs->questions_count }} questions · {{ str_replace('_',' ',$qs->type) }}</div>
                </div>
            </div>
            @empty
            <p class="text-surface-500 text-sm">No question sets created yet.</p>
            @endforelse
        </div>

    </div>

    {{-- Members Table --}}
    <div>
        <h2 class="text-sm font-semibold text-surface-300 uppercase tracking-wide mb-3">Members ({{ $members->count() }})</h2>
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full min-w-[480px] text-sm">
                <thead>
                    <tr class="border-b border-surface-800">
                        <th class="text-left px-4 py-3 text-surface-400 font-medium">Name</th>
                        <th class="text-left px-4 py-3 text-surface-400 font-medium">Email</th>
                        <th class="text-left px-4 py-3 text-surface-400 font-medium">Role</th>
                        <th class="text-left px-4 py-3 text-surface-400 font-medium">Tests</th>
                        <th class="text-left px-4 py-3 text-surface-400 font-medium">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-800">
                    @forelse($members as $m)
                    <tr class="hover:bg-surface-900">
                        <td class="px-4 py-3 text-surface-200">{{ $m->name }}</td>
                        <td class="px-4 py-3 text-surface-400 text-xs">{{ $m->email }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs capitalize bg-surface-800 text-surface-300 px-2 py-0.5 rounded">{{ $m->institute_role }}</span>
                        </td>
                        <td class="px-4 py-3 text-surface-300">{{ $m->tests->count() }}</td>
                        <td class="px-4 py-3 text-surface-500 text-xs">{{ $m->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-surface-500">No members yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

</div>
</div>
</x-app-layout>
