<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-surface-50">Global Questions <span class="text-surface-500 font-normal text-lg">({{ $questions->total() }})</span></h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.question-sets.index') }}" class="btn-secondary text-sm">Question Sets</a>
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary text-sm">← Dashboard</a>
            <a href="{{ route('admin.questions.create') }}" class="btn-primary text-sm">+ New Question</a>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex gap-3 mb-6">
        <select name="type" class="bg-surface-900 border border-surface-700 rounded-lg px-3 py-2 text-surface-300 text-sm">
            <option value="">All types</option>
            @foreach($types as $t)
            <option value="{{ $t }}" @selected(request('type')==$t)>{{ str_replace('_', ' ', ucfirst($t)) }}</option>
            @endforeach
        </select>
        <select name="difficulty" class="bg-surface-900 border border-surface-700 rounded-lg px-3 py-2 text-surface-300 text-sm">
            <option value="">All difficulties</option>
            <option value="easy" @selected(request('difficulty')=='easy')>Easy</option>
            <option value="medium" @selected(request('difficulty')=='medium')>Medium</option>
            <option value="hard" @selected(request('difficulty')=='hard')>Hard</option>
        </select>
        <button type="submit" class="btn-primary text-sm px-4">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[480px] text-sm">
            <thead>
                <tr class="border-b border-surface-800">
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">ID</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Type</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Title</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Created</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-800">
                @foreach($questions as $q)
                <tr class="hover:bg-surface-900">
                    <td class="px-4 py-3 text-surface-500 font-mono text-xs">{{ $q->id }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs bg-surface-800 text-surface-300 px-2 py-0.5 rounded">{{ str_replace('_', ' ', $q->type) }}</span>
                    </td>
                    <td class="px-4 py-3 text-surface-200 max-w-md truncate">{{ $q->title ?? $q->question_text }}</td>
                    <td class="px-4 py-3 text-surface-500 text-xs">{{ $q->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 flex items-center gap-3">
                        <a href="{{ route('admin.questions.edit', $q) }}" class="text-xs text-indigo-400 hover:text-indigo-300">Edit</a>
                        <form method="POST" action="{{ route('admin.questions.destroy', $q) }}"
                            onsubmit="return confirm('Delete this question?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-300">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">{{ $questions->links() }}</div>

</div>
</div>
</x-app-layout>
