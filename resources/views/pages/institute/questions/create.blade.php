<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-3xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-surface-50">New Question</h1>
        <a href="{{ route('institute.questions.index') }}" class="btn-secondary text-sm">← Question Bank</a>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3 text-red-400 text-sm mb-6">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('institute.questions.store') }}" class="card p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm text-surface-400 mb-1">Type <span class="text-red-400">*</span></label>
                <select name="type" id="type" required
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
                    <option value="">Select type…</option>
                    @foreach(['writing','speaking','listening','reading'] as $t)
                        <option value="{{ $t }}" @selected(old('type') == $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-surface-400 mb-1">Category <span class="text-red-400">*</span></label>
                <select name="category" id="category" required
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
                    <option value="">Select type first…</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm text-surface-400 mb-1">Title / Prompt <span class="text-red-400">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Short description of this question"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm text-surface-400 mb-1">Content <span class="text-red-400">*</span></label>
                <textarea name="content" rows="6" required placeholder="Full question text, passage, or prompt…"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">{{ old('content') }}</textarea>
            </div>

            <div>
                <label class="block text-sm text-surface-400 mb-1">Media URL <span class="text-surface-600 text-xs">(optional)</span></label>
                <input type="url" name="media_url" value="{{ old('media_url') }}" placeholder="https://…"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm text-surface-400 mb-1">Difficulty</label>
                <select name="difficulty"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
                    <option value="">Not set</option>
                    <option value="easy" @selected(old('difficulty')=='easy')>Easy</option>
                    <option value="medium" @selected(old('difficulty')=='medium')>Medium</option>
                    <option value="hard" @selected(old('difficulty')=='hard')>Hard</option>
                </select>
            </div>

            <div>
                <label class="block text-sm text-surface-400 mb-1">Time Limit (seconds)</label>
                <input type="number" name="time_limit" value="{{ old('time_limit') }}" min="1"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm text-surface-400 mb-1">Min Words</label>
                <input type="number" name="min_words" value="{{ old('min_words') }}" min="1"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary text-sm px-6">Create Question</button>
            <a href="{{ route('institute.questions.index') }}" class="btn-secondary text-sm">Cancel</a>
        </div>
    </form>

</div>
</div>

<script>
const categories = {
    writing:   ['writing_academic_task1','writing_academic_task2','writing_general_task1','writing_general_task2'],
    speaking:  ['speaking_part1','speaking_part2','speaking_part3'],
    listening: ['listening_academic','listening_general'],
    reading:   ['reading_academic','reading_general'],
};
const oldCat = "{{ old('category') }}";

document.getElementById('type').addEventListener('change', function () {
    const sel = document.getElementById('category');
    sel.innerHTML = '<option value="">Select…</option>';
    (categories[this.value] || []).forEach(c => {
        const label = c.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase());
        sel.innerHTML += `<option value="${c}" ${c===oldCat?'selected':''}>${label}</option>`;
    });
});

window.addEventListener('DOMContentLoaded', () => {
    const t = document.getElementById('type');
    if (t.value) t.dispatchEvent(new Event('change'));
});
</script>
</x-app-layout>
