<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-surface-50">Users</h1>
        <a href="{{ route('admin.dashboard') }}" class="btn-secondary text-sm">← Dashboard</a>
    </div>

    {{-- Search & Filter --}}
    <form method="GET" class="flex gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}"
            placeholder="Search name or email…"
            class="flex-1 bg-surface-900 border border-surface-700 rounded-lg px-4 py-2 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
        <select name="filter" class="bg-surface-900 border border-surface-700 rounded-lg px-3 py-2 text-surface-300 text-sm">
            <option value="">All users</option>
            <option value="pro" @selected(request('filter')=='pro')>Pro</option>
            <option value="free" @selected(request('filter')=='free')>Free</option>
            <option value="admin" @selected(request('filter')=='admin')>Admin</option>
        </select>
        <button type="submit" class="btn-primary text-sm px-4">Filter</button>
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[480px] text-sm">
            <thead>
                <tr class="border-b border-surface-800">
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">User</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Plan</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Credits</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Joined</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-800">
                @foreach($users as $user)
                <tr class="hover:bg-surface-900 transition-colors">
                    <td class="px-4 py-3">
                        <div class="font-medium text-surface-100">{{ $user->name }}</div>
                        <div class="text-surface-500 text-xs">{{ $user->email }}</div>
                        @if($user->is_admin)<span class="text-xs bg-red-500/20 text-red-400 px-1.5 py-0.5 rounded">Admin</span>@endif
                    </td>
                    <td class="px-4 py-3">
                        @if($user->is_pro)
                            <span class="text-xs bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full">Pro</span>
                        @else
                            <span class="text-xs bg-surface-700 text-surface-400 px-2 py-0.5 rounded-full">Free</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-surface-300">{{ $user->test_credits ?? 0 }}</td>
                    <td class="px-4 py-3 text-surface-400 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-xs text-indigo-400 hover:text-indigo-300">View</a>
                            <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}">
                                @csrf
                                <button class="text-xs text-amber-400 hover:text-amber-300">
                                    {{ $user->is_admin ? 'Revoke Admin' : 'Make Admin' }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>

</div>
</div>
</x-app-layout>
