<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-surface-50">{{ $institute->name }}</h1>
            <p class="text-surface-400 text-sm mt-1">
                {{ $institute->seats_used }} / {{ $institute->seat_limit }} seats used &middot;
                <span class="capitalize text-indigo-400">{{ $institute->plan }} plan</span>
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('institute.questions.index') }}" class="btn-secondary text-sm">Question Bank</a>
            <a href="{{ route('institute.question-sets.index') }}" class="btn-secondary text-sm">Question Sets</a>
            <a href="{{ route('institute.assignments.index') }}" class="btn-secondary text-sm">Assignments</a>
            <a href="{{ route('institute.pricing') }}" class="btn-secondary text-sm">Upgrade Plan</a>
            <button onclick="document.getElementById('createBatch').classList.toggle('hidden')"
                class="btn-primary text-sm">
                + New Batch
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-4 py-3 text-emerald-400 text-sm mb-6">{{ session('success') }}</div>
    @endif

    {{-- Create Batch Form --}}
    <div id="createBatch" class="hidden card p-6 mb-8">
        <h3 class="font-semibold text-surface-200 mb-4">Create New Batch</h3>
        <form method="POST" action="{{ route('institute.batch.create') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-sm text-surface-400 mb-1">Batch Name</label>
                <input type="text" name="name" required placeholder="IELTS April 2026"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-4 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm text-surface-400 mb-1">Test Type</label>
                <select name="test_type" class="w-full bg-surface-900 border border-surface-700 rounded-lg px-4 py-2.5 text-surface-100 text-sm">
                    <option value="academic">Academic</option>
                    <option value="general">General Training</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-surface-400 mb-1">Target Band</label>
                <input type="number" name="target_band" step="0.5" min="1" max="9" placeholder="7.0"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-4 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm text-surface-400 mb-1">Exam Date</label>
                <input type="date" name="exam_date"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-4 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm text-surface-400 mb-1">Description</label>
                <textarea name="description" rows="2" placeholder="Optional notes about this batch…"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-4 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500"></textarea>
            </div>
            <div class="md:col-span-2 flex gap-3">
                <button type="submit" class="btn-primary text-sm">Create Batch</button>
                <button type="button" onclick="document.getElementById('createBatch').classList.add('hidden')"
                    class="btn-secondary text-sm">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-indigo-400">{{ $members->count() }}</div>
            <div class="text-xs text-surface-400 mt-1">Total Students</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-amber-400">{{ $batches->count() }}</div>
            <div class="text-xs text-surface-400 mt-1">Batches</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-emerald-400">{{ $totalTests }}</div>
            <div class="text-xs text-surface-400 mt-1">Tests Taken</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-surface-300">{{ $institute->seat_limit - $institute->seats_used }}</div>
            <div class="text-xs text-surface-400 mt-1">Seats Remaining</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Batches --}}
        <div class="lg:col-span-2">
            <h2 class="font-semibold text-surface-200 mb-4">Batches</h2>
            @if($batches->isEmpty())
            <div class="card p-8 text-center text-surface-500">No batches yet. Create your first batch above.</div>
            @else
            <div class="space-y-3">
                @foreach($batches as $batch)
                <a href="{{ route('institute.batch.show', $batch) }}"
                    class="card p-4 flex items-center justify-between hover:bg-surface-800 transition-colors block">
                    <div>
                        <div class="font-medium text-surface-100">{{ $batch->name }}</div>
                        <div class="text-surface-400 text-xs mt-0.5">
                            {{ ucfirst($batch->test_type) }}
                            @if($batch->target_band) &middot; Target: {{ $batch->target_band }} @endif
                            @if($batch->exam_date) &middot; Exam: {{ $batch->exam_date->format('d M Y') }} @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-surface-300 font-semibold">{{ $batch->students_count }}</div>
                        <div class="text-surface-500 text-xs">students</div>
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Recent Students --}}
        <div>
            <h2 class="font-semibold text-surface-200 mb-4">Recent Students</h2>
            <div class="card divide-y divide-surface-800">
                @forelse($members->take(8) as $member)
                <div class="px-4 py-3 flex items-center justify-between">
                    <div>
                        <div class="text-sm text-surface-200">{{ $member->name }}</div>
                        <div class="text-xs text-surface-500">{{ $member->email }}</div>
                    </div>
                    <div class="text-xs text-surface-400">{{ $member->tests->count() }} tests</div>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-surface-500 text-sm">No students yet</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
</div>
</x-app-layout>
