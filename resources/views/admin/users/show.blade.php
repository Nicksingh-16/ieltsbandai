<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-4xl mx-auto px-4 py-8">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.users') }}" class="btn-secondary text-sm">← Users</a>
        <h1 class="text-2xl font-bold text-surface-50">{{ $user->name }}</h1>
        @if($user->is_admin)<span class="text-xs bg-red-500/20 text-red-400 px-2 py-1 rounded">Admin</span>@endif
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-4 py-3 text-emerald-400 text-sm mb-6">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        {{-- Info --}}
        <div class="card p-5 col-span-2 space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-surface-400">Email</span><span class="text-surface-100">{{ $user->email }}</span></div>
            <div class="flex justify-between"><span class="text-surface-400">Plan</span><span class="text-surface-100">{{ $user->is_pro ? 'Pro' : 'Free' }}</span></div>
            <div class="flex justify-between"><span class="text-surface-400">Credits</span><span class="text-surface-100">{{ $user->test_credits ?? 0 }}</span></div>
            <div class="flex justify-between"><span class="text-surface-400">Verified</span><span class="text-surface-100">{{ $user->email_verified_at ? 'Yes' : 'No' }}</span></div>
            <div class="flex justify-between"><span class="text-surface-400">Institute</span><span class="text-surface-100">{{ optional($user->institute)->name ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-surface-400">Joined</span><span class="text-surface-100">{{ $user->created_at->format('d M Y') }}</span></div>
        </div>

        {{-- Actions --}}
        <div class="space-y-3">
            <form method="POST" action="{{ route('admin.users.credits', $user) }}" class="card p-4">
                @csrf
                <label class="text-xs text-surface-400 block mb-2">Add Credits</label>
                <div class="flex gap-2">
                    <input type="number" name="credits" min="1" max="500" value="10"
                        class="flex-1 bg-surface-900 border border-surface-700 rounded px-3 py-2 text-sm text-surface-100">
                    <button type="submit" class="btn-primary text-sm px-3">Add</button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.users.suspend', $user) }}" class="card p-4"
                onsubmit="return confirm('Suspend this user?')">
                @csrf
                <button type="submit" class="w-full text-sm text-red-400 hover:text-red-300 text-left">
                    Suspend user (removes email verification)
                </button>
            </form>
        </div>
    </div>

    {{-- Recent Tests --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-surface-800">
            <h3 class="font-semibold text-surface-200 text-sm">Recent Tests ({{ $user->tests->count() }} total)</h3>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full min-w-[480px] text-sm">
            <thead>
                <tr class="border-b border-surface-800">
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Type</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Band</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Status</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-800">
                @forelse($recentTests as $test)
                <tr>
                    <td class="px-4 py-3 text-surface-300">{{ str_replace('_', ' ', ucfirst($test->type)) }}</td>
                    <td class="px-4 py-3 font-semibold text-amber-400">{{ $test->overall_band ?? '—' }}</td>
                    <td class="px-4 py-3 text-surface-400">{{ $test->status }}</td>
                    <td class="px-4 py-3 text-surface-500 text-xs">{{ $test->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-surface-500">No tests yet</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

</div>
</div>
</x-app-layout>
