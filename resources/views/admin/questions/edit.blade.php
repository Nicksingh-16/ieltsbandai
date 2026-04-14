<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-3xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-surface-50">Edit Question <span class="text-surface-500 font-normal text-lg">#{{ $question->id }}</span></h1>
        <a href="{{ route('admin.questions') }}" class="btn-secondary text-sm">← Questions</a>
    </div>

    @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3 text-red-400 text-sm mb-6">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.questions.update', $question) }}" class="card p-6 space-y-5">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm text-surface-400 mb-1">Type <span class="text-red-400">*</span></label>
                <select name="type" id="type" required
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
                    @foreach(['writing','speaking','listening','reading'] as $t)
                        <option value="{{ $t }}" @selected(old('type', $question->type) == $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-surface-400 mb-1">Category <span class="text-red-400">*</span></label>
                <select name="category" id="category" required
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm text-surface-400 mb-1">Title / Prompt <span class="text-red-400">*</span></label>
                <input type="text" name="title" value="{{ old('title', $question->title) }}" required
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm text-surface-400 mb-1">Content <span class="text-red-400">*</span></label>
                <textarea name="content" rows="6" required
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">{{ old('content', $question->content) }}</textarea>
            </div>

            <div>
                <label class="block text-sm text-surface-400 mb-1">Media URL</label>
                <input type="url" name="media_url" value="{{ old('media_url', $question->media_url) }}"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm text-surface-400 mb-1">Difficulty</label>
                <select name="difficulty"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
                    <option value="">Not set</option>
                    @foreach(['easy','medium','hard'] as $d)
                        <option value="{{ $d }}" @selected(old('difficulty', $question->metadata['difficulty'] ?? '') == $d)>{{ ucfirst($d) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm text-surface-400 mb-1">Time Limit (seconds)</label>
                <input type="number" name="time_limit" value="{{ old('time_limit', $question->time_limit) }}" min="1"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-sm text-surface-400 mb-1">Min Words</label>
                <input type="number" name="min_words" value="{{ old('min_words', $question->min_words) }}" min="1"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-3 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
            </div>

            <div class="flex items-center gap-3">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" id="active" @checked(old('active', $question->active))
                    class="rounded border-surface-600 bg-surface-800 text-indigo-500">
                <label for="active" class="text-sm text-surface-300">Active (visible in question bank)</label>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="btn-primary text-sm px-6">Save Changes</button>
            <a href="{{ route('admin.questions') }}" class="btn-secondary text-sm">Cancel</a>
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
const currentCat = "{{ old('category', $question->category) }}";

function populateCategories(type) {
    const sel = document.getElementById('category');
    sel.innerHTML = '';
    (categories[type] || []).forEach(c => {
        const label = c.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase());
        sel.innerHTML += `<option value="${c}" ${c===currentCat?'selected':''}>${label}</option>`;
    });
}

document.getElementById('type').addEventListener('change', function () {
    populateCategories(this.value);
});

window.addEventListener('DOMContentLoaded', () => {
    populateCategories(document.getElementById('type').value);
});
</script>
</x-app-layout>
