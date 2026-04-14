<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-surface-50">Question Bank</h1>
            <p class="text-surface-400 text-sm mt-1">{{ $institute->name }} · private questions for your students</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('institute.dashboard') }}" class="btn-secondary text-sm">← Dashboard</a>
            @if(Auth::user()->isTeacher())
            <a href="{{ route('institute.questions.create') }}" class="btn-primary text-sm">+ New Question</a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-4 py-3 text-emerald-400 text-sm mb-6">{{ session('success') }}</div>
    @endif

    {{-- Filter --}}
    <form method="GET" class="flex gap-3 mb-6">
        <select name="type" class="bg-surface-900 border border-surface-700 rounded-lg px-3 py-2 text-surface-300 text-sm">
            <option value="">All types</option>
            @foreach($types as $t)
                <option value="{{ $t }}" @selected(request('type') == $t)>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary text-sm px-4">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[480px] text-sm">
            <thead>
                <tr class="border-b border-surface-800">
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Title</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Type</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Category</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Difficulty</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Created</th>
                    @if(Auth::user()->isTeacher())
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-800">
                @forelse($questions as $q)
                <tr class="hover:bg-surface-900">
                    <td class="px-4 py-3 text-surface-200 max-w-sm truncate">{{ $q->title }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs bg-surface-800 text-surface-300 px-2 py-0.5 rounded">{{ ucfirst($q->type) }}</span>
                    </td>
                    <td class="px-4 py-3 text-surface-400 text-xs">{{ str_replace('_',' ', $q->category) }}</td>
                    <td class="px-4 py-3">
                        @php $diff = $q->metadata['difficulty'] ?? null; @endphp
                        @if($diff)
                        <span class="text-xs px-2 py-0.5 rounded
                            {{ ['easy'=>'bg-emerald-500/20 text-emerald-400','medium'=>'bg-amber-500/20 text-amber-400','hard'=>'bg-red-500/20 text-red-400'][$diff] ?? '' }}">
                            {{ ucfirst($diff) }}
                        </span>
                        @else<span class="text-surface-600 text-xs">—</span>@endif
                    </td>
                    <td class="px-4 py-3 text-surface-500 text-xs">{{ $q->created_at->format('d M Y') }}</td>
                    @if(Auth::user()->isTeacher())
                    <td class="px-4 py-3 flex items-center gap-3">
                        <a href="{{ route('institute.questions.edit', $q) }}" class="text-xs text-indigo-400 hover:text-indigo-300">Edit</a>
                        <form method="POST" action="{{ route('institute.questions.destroy', $q) }}"
                            onsubmit="return confirm('Delete this question?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-surface-500">No questions yet. <a href="{{ route('institute.questions.create') }}" class="text-indigo-400">Create one →</a></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">{{ $questions->links() }}</div>

</div>
</div>
</x-app-layout>
