<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('admin.question-sets.index') }}" class="text-surface-500 hover:text-surface-300 text-sm">← Question Sets</a>
            </div>
            <h1 class="text-2xl font-bold text-surface-50">{{ $set->name }}</h1>
            <div class="flex items-center gap-3 mt-1 text-sm text-surface-400">
                <span class="bg-surface-800 px-2 py-0.5 rounded text-xs">{{ str_replace('_',' ', $set->type) }}</span>
                <span>{{ $set->duration_minutes }}m</span>
                <span>{{ $set->questions->count() }} questions</span>
                @if($set->is_active)
                    <span class="text-emerald-400 text-xs">● Active</span>
                @else
                    <span class="text-surface-500 text-xs">● Inactive</span>
                @endif
            </div>
            @if($set->description)
            <p class="text-surface-400 text-sm mt-2">{{ $set->description }}</p>
            @endif
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.questions.create') }}" class="btn-secondary text-sm">+ New Question</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-4 py-3 text-emerald-400 text-sm mb-6">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- Questions in Set --}}
        <div class="lg:col-span-3">
            <h2 class="text-sm font-semibold text-surface-300 uppercase tracking-wide mb-3">Questions in this set</h2>
            <div class="card overflow-hidden">
                @forelse($set->questions as $q)
                <div class="flex items-start gap-4 px-4 py-3 border-b border-surface-800 last:border-0">
                    <span class="text-surface-600 font-mono text-xs pt-0.5 w-5 shrink-0">{{ $loop->iteration }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-surface-200 text-sm font-medium truncate">{{ $q->title }}</div>
                        <div class="flex gap-2 mt-1">
                            <span class="text-xs bg-surface-800 text-surface-400 px-2 py-0.5 rounded">{{ str_replace('_',' ', $q->category) }}</span>
                            @if($q->metadata['difficulty'] ?? null)
                            <span class="text-xs px-2 py-0.5 rounded
                                {{ ['easy'=>'bg-emerald-500/20 text-emerald-400','medium'=>'bg-amber-500/20 text-amber-400','hard'=>'bg-red-500/20 text-red-400'][$q->metadata['difficulty']] ?? '' }}">
                                {{ ucfirst($q->metadata['difficulty']) }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.question-sets.remove-question', [$set, $q]) }}"
                        onsubmit="return confirm('Remove from set?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-400 hover:text-red-300 shrink-0">Remove</button>
                    </form>
                </div>
                @empty
                <div class="px-4 py-8 text-center text-surface-500 text-sm">No questions yet — add from the panel on the right.</div>
                @endforelse
            </div>
        </div>

        {{-- Add Questions Panel --}}
        <div class="lg:col-span-2">
            <h2 class="text-sm font-semibold text-surface-300 uppercase tracking-wide mb-3">Add Questions</h2>
            <div class="card p-4">
                <form method="POST" action="{{ route('admin.question-sets.add-question', $set) }}" class="flex gap-2 mb-4">
                    @csrf
                    <select name="question_id" required
                        class="flex-1 bg-surface-900 border border-surface-700 rounded-lg px-3 py-2 text-surface-200 text-sm focus:outline-none focus:border-indigo-500">
                        <option value="">Select a question…</option>
                        @foreach($availableQuestions as $q)
                        <option value="{{ $q->id }}">[{{ strtoupper(substr($q->type,0,1)) }}] {{ Str::limit($q->title, 55) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-primary text-sm px-4 shrink-0">Add</button>
                </form>
                <p class="text-surface-500 text-xs">
                    {{ $availableQuestions->count() }} global {{ $set->type !== 'full_mock' ? $set->type : 'all-type' }} questions available.
                    <a href="{{ route('admin.questions.create') }}" class="text-indigo-400">Create more →</a>
                </p>
            </div>
        </div>

    </div>

</div>
</div>
</x-app-layout>
