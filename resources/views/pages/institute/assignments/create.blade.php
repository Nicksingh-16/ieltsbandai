<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-2xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-surface-50">Assign a Test</h1>
        <a href="{{ route('institute.assignments.index') }}" class="btn-secondary text-sm">← Assignments</a>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3 text-red-400 text-sm mb-6">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if($templates->isEmpty())
        <div class="card p-8 text-center">
            <p class="text-surface-400 mb-4">You need at least one Question Set before assigning a test.</p>
            <a href="{{ route('institute.question-sets.create') }}" class="btn-primary text-sm">Create Question Set →</a>
        </div>
    @else
    <form method="POST" action="{{ route('institute.assignments.store') }}" class="card p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm text-surface-400 mb-1">Assignment Title <span class="text-red-400">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required
                placeholder="e.g. April Writing Practice — Task 2"
                class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
        </div>

        <div class="grid grid-cols-2 gap-5">
            <div>
                <label class="block text-sm text-surface-400 mb-1">Question Set <span class="text-red-400">*</span></label>
                <select name="test_template_id" required
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
                    <option value="">Select…</option>
                    @foreach($templates as $t)
                        <option value="{{ $t->id }}" @selected(old('test_template_id') == $t->id)>
                            {{ $t->name }} ({{ str_replace('_',' ',$t->type) }} · {{ $t->questions()->count() }}q)
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-surface-400 mb-1">Batch <span class="text-red-400">*</span></label>
                <select name="batch_id" required
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
                    <option value="">Select…</option>
                    @foreach($batches as $b)
                        <option value="{{ $b->id }}" @selected(old('batch_id') == $b->id)>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm text-surface-400 mb-1">Instructions <span class="text-surface-600 text-xs">(optional — shown to students)</span></label>
            <textarea name="instructions" rows="3"
                placeholder="Any special notes or focus areas for this test…"
                class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">{{ old('instructions') }}</textarea>
        </div>

        <div>
            <label class="block text-sm text-surface-400 mb-1">Due Date <span class="text-surface-600 text-xs">(optional)</span></label>
            <input type="datetime-local" name="due_date" value="{{ old('due_date') }}"
                class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
        </div>

        <div class="flex gap-6">
            <label class="flex items-center gap-2 text-sm text-surface-300 cursor-pointer">
                <input type="hidden" name="is_mandatory" value="0">
                <input type="checkbox" name="is_mandatory" value="1" @checked(old('is_mandatory', true))
                    class="rounded border-surface-600 bg-surface-800 text-indigo-500">
                Mandatory
            </label>
            <label class="flex items-center gap-2 text-sm text-surface-300 cursor-pointer">
                <input type="hidden" name="allows_retake" value="0">
                <input type="checkbox" name="allows_retake" value="1" @checked(old('allows_retake'))
                    class="rounded border-surface-600 bg-surface-800 text-indigo-500">
                Allow Retake
            </label>
        </div>

        <div class="bg-surface-900 rounded-lg px-4 py-3 text-xs text-surface-400">
            All students currently in the selected batch will be enrolled automatically.
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary text-sm px-6">Assign to Batch</button>
            <a href="{{ route('institute.assignments.index') }}" class="btn-secondary text-sm">Cancel</a>
        </div>
    </form>
    @endif

</div>
</div>
</x-app-layout>
