<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-2xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-surface-50">New Question Set</h1>
        <a href="{{ route('institute.question-sets.index') }}" class="btn-secondary text-sm">← Question Sets</a>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3 text-red-400 text-sm mb-6">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('institute.question-sets.store') }}" class="card p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm text-surface-400 mb-1">Set Name <span class="text-red-400">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. April Batch — Writing Practice"
                class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
        </div>

        <div>
            <label class="block text-sm text-surface-400 mb-1">Description</label>
            <textarea name="description" rows="3" placeholder="What is this set for?"
                class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm text-surface-400 mb-1">Test Type <span class="text-red-400">*</span></label>
                <select name="type" required
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
                    @foreach(['writing','speaking','listening','reading','full_mock'] as $t)
                        <option value="{{ $t }}" @selected(old('type') == $t)>{{ str_replace('_',' ', ucfirst($t)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-surface-400 mb-1">Duration (minutes) <span class="text-red-400">*</span></label>
                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" min="1" max="480" required
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary text-sm px-6">Create & Add Questions</button>
            <a href="{{ route('institute.question-sets.index') }}" class="btn-secondary text-sm">Cancel</a>
        </div>
    </form>

</div>
</div>
</x-app-layout>
