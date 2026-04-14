<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-surface-50">Question Sets</h1>
            <p class="text-surface-400 text-sm mt-1">{{ $institute->name }} · curated sets to assign to batches</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('institute.dashboard') }}" class="btn-secondary text-sm">← Dashboard</a>
            @if(Auth::user()->isTeacher())
            <a href="{{ route('institute.question-sets.create') }}" class="btn-primary text-sm">+ New Set</a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-4 py-3 text-emerald-400 text-sm mb-6">{{ session('success') }}</div>
    @endif

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[600px] text-sm">
            <thead>
                <tr class="border-b border-surface-800">
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Name</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Type</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Questions</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Duration</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Status</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Created</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-800">
                @forelse($sets as $set)
                <tr class="hover:bg-surface-900">
                    <td class="px-4 py-3">
                        <a href="{{ route('institute.question-sets.show', $set) }}" class="text-surface-100 hover:text-indigo-400 font-medium">{{ $set->name }}</a>
                        @if($set->description)
                        <p class="text-surface-500 text-xs mt-0.5 truncate max-w-xs">{{ $set->description }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs bg-surface-800 text-surface-300 px-2 py-0.5 rounded">{{ str_replace('_',' ', $set->type) }}</span>
                    </td>
                    <td class="px-4 py-3 text-surface-300">{{ $set->questions_count }}</td>
                    <td class="px-4 py-3 text-surface-400 text-xs">{{ $set->duration_minutes }}m</td>
                    <td class="px-4 py-3">
                        @if($set->is_active)
                            <span class="text-xs bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full">Active</span>
                        @else
                            <span class="text-xs bg-surface-800 text-surface-500 px-2 py-0.5 rounded-full">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-surface-500 text-xs">{{ $set->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 flex items-center gap-3">
                        <a href="{{ route('institute.question-sets.show', $set) }}" class="text-xs text-indigo-400 hover:text-indigo-300">Manage</a>
                        @if(Auth::user()->isTeacher())
                        <form method="POST" action="{{ route('institute.question-sets.destroy', $set) }}"
                            onsubmit="return confirm('Delete this question set?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-300">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-surface-500">No question sets yet. <a href="{{ route('institute.question-sets.create') }}" class="text-indigo-400">Create one →</a></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">{{ $sets->links() }}</div>

</div>
</div>
</x-app-layout>
