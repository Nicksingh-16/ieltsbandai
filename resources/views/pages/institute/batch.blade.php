<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('institute.dashboard') }}" class="btn-secondary text-sm">← Dashboard</a>
        <a href="{{ route('institute.batch.analytics', $batch) }}" class="btn-secondary text-sm">Analytics →</a>
        <div>
            <h1 class="text-2xl font-bold text-surface-50">{{ $batch->name }}</h1>
            <p class="text-surface-400 text-sm">{{ ucfirst($batch->test_type) }} Training
                @if($batch->target_band) &middot; Target: {{ $batch->target_band }} @endif
                @if($batch->exam_date) &middot; Exam: {{ $batch->exam_date->format('d M Y') }} @endif
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-4 py-3 text-emerald-400 text-sm mb-6">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3 text-red-400 text-sm mb-6">{{ session('error') }}</div>
    @endif

    {{-- Add Student / Bulk Import --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
        <form method="POST" action="{{ route('institute.batch.invite', $batch) }}" class="card p-5">
            @csrf
            <h3 class="font-semibold text-surface-200 text-sm mb-3">Invite Single Student</h3>
            <div class="flex gap-2">
                <input type="email" name="email" required placeholder="student@email.com"
                    class="flex-1 bg-surface-900 border border-surface-700 rounded-lg px-4 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
                <button type="submit" class="btn-primary text-sm px-4">Invite</button>
            </div>
            <p class="text-xs text-surface-500 mt-2">If no account exists, one is created and login details are emailed.</p>
        </form>

        <form method="POST" action="{{ route('institute.batch.import', $batch) }}" class="card p-5" enctype="multipart/form-data">
            @csrf
            <h3 class="font-semibold text-surface-200 text-sm mb-3">Bulk Import via CSV</h3>
            <div class="flex gap-2">
                <input type="file" name="csv" accept=".csv,.txt" required
                    class="flex-1 text-sm text-surface-400 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:bg-indigo-500/20 file:text-indigo-400 hover:file:bg-indigo-500/30">
                <button type="submit" class="btn-primary text-sm px-4">Import</button>
            </div>
            <p class="text-xs text-surface-500 mt-2">CSV format: <code class="text-surface-400">email, name</code> (header row optional)</p>
        </form>
    </div>

    {{-- Students Table --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-surface-800 flex items-center justify-between">
            <h3 class="font-semibold text-surface-200 text-sm">Students ({{ count($students) }})</h3>
            <div class="flex gap-4 text-xs text-surface-400">
                <span>L = Listening &middot; R = Reading &middot; W = Writing &middot; S = Speaking</span>
            </div>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[480px]">
            <thead>
                <tr class="border-b border-surface-800">
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Student</th>
                    <th class="text-center px-3 py-3 text-surface-400 font-medium">L</th>
                    <th class="text-center px-3 py-3 text-surface-400 font-medium">R</th>
                    <th class="text-center px-3 py-3 text-surface-400 font-medium">W</th>
                    <th class="text-center px-3 py-3 text-surface-400 font-medium">S</th>
                    <th class="text-center px-3 py-3 text-surface-400 font-medium">Overall</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-800">
                @forelse($students as $row)
                @php
                    $bands = array_filter([$row['listening'], $row['reading'], $row['writing'], $row['speaking']]);
                    $overall = count($bands) ? round(array_sum($bands) / count($bands) * 2) / 2 : null;
                @endphp
                <tr class="hover:bg-surface-900">
                    <td class="px-4 py-3">
                        <div class="text-surface-200 font-medium">{{ $row['user']->name }}</div>
                        <div class="text-surface-500 text-xs">{{ $row['user']->email }}</div>
                    </td>
                    @foreach(['listening','reading','writing','speaking'] as $mod)
                    <td class="px-3 py-3 text-center">
                        @if($row[$mod])
                            <span class="font-semibold {{ $row[$mod] >= 7 ? 'text-emerald-400' : ($row[$mod] >= 6 ? 'text-amber-400' : 'text-red-400') }}">
                                {{ $row[$mod] }}
                            </span>
                        @else
                            <span class="text-surface-600">—</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="px-3 py-3 text-center font-bold text-indigo-400">
                        {{ $overall ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-surface-500">No students in this batch yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>{{-- /overflow-x-auto --}}
    </div>

</div>
</div>
</x-app-layout>
