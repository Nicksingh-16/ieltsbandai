<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('institute.assignments.index') }}" class="text-surface-500 hover:text-surface-300 text-sm">← Assignments</a>
            <h1 class="text-2xl font-bold text-surface-50 mt-2">{{ $assignment->title }}</h1>
            <div class="flex flex-wrap items-center gap-3 mt-1 text-sm text-surface-400">
                <span class="bg-surface-800 px-2 py-0.5 rounded text-xs">{{ str_replace('_',' ', $assignment->template->type) }}</span>
                <span>{{ optional($assignment->batch)->name ?? 'No batch' }}</span>
                @if($assignment->due_date)
                <span class="{{ $assignment->isOverdue() ? 'text-red-400' : '' }}">Due {{ $assignment->due_date->format('d M Y, g:i A') }}</span>
                @endif
                @if($assignment->is_mandatory)
                <span class="text-xs bg-red-500/20 text-red-400 px-2 py-0.5 rounded-full">Mandatory</span>
                @endif
            </div>
            @if($assignment->instructions)
            <p class="text-surface-400 text-sm mt-2 max-w-xl">{{ $assignment->instructions }}</p>
            @endif
        </div>
        <div class="flex gap-3 shrink-0">
            @if(Auth::user()->isTeacher())
            <form method="POST" action="{{ route('institute.assignments.toggle', $assignment) }}">
                @csrf
                <button class="btn-secondary text-sm">
                    {{ $assignment->status === 'active' ? 'Close Assignment' : 'Reopen' }}
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-4 py-3 text-emerald-400 text-sm mb-6">{{ session('success') }}</div>
    @endif

    {{-- Summary Cards --}}
    @php
        $total     = $records->count();
        $pending   = $records->where('status', 'pending')->count();
        $started   = $records->where('status', 'started')->count();
        $completed = $records->where('status', 'completed')->count();
        $pct       = $total ? round(($completed / $total) * 100) : 0;
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-surface-100">{{ $total }}</div>
            <div class="text-xs text-surface-400 mt-1">Total Students</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-amber-400">{{ $pending }}</div>
            <div class="text-xs text-surface-400 mt-1">Pending</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-blue-400">{{ $started }}</div>
            <div class="text-xs text-surface-400 mt-1">In Progress</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-emerald-400">{{ $completed }} <span class="text-sm font-normal text-surface-500">({{ $pct }}%)</span></div>
            <div class="text-xs text-surface-400 mt-1">Completed</div>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="bg-surface-800 rounded-full h-2 mb-8">
        <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width:{{ $pct }}%"></div>
    </div>

    {{-- Student Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[600px] text-sm">
            <thead>
                <tr class="border-b border-surface-800">
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Student</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Status</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Started</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Completed</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Band</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Result</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-800">
                @foreach($records as $r)
                <tr class="hover:bg-surface-900">
                    <td class="px-4 py-3">
                        <div class="text-surface-200">{{ $r->student->name }}</div>
                        <div class="text-surface-500 text-xs">{{ $r->student->email }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @php $colors = ['pending'=>'text-surface-400','started'=>'text-blue-400','completed'=>'text-emerald-400','skipped'=>'text-surface-600']; @endphp
                        <span class="text-xs capitalize {{ $colors[$r->status] ?? '' }}">{{ $r->status }}</span>
                    </td>
                    <td class="px-4 py-3 text-surface-500 text-xs">{{ $r->started_at?->format('d M, g:i A') ?? '—' }}</td>
                    <td class="px-4 py-3 text-surface-500 text-xs">{{ $r->completed_at?->format('d M, g:i A') ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($r->test?->overall_band)
                        <span class="text-emerald-400 font-semibold">{{ number_format($r->test->overall_band, 1) }}</span>
                        @else<span class="text-surface-600 text-xs">—</span>@endif
                    </td>
                    <td class="px-4 py-3">
                        @if($r->test && $r->test->status === 'completed')
                        <a href="{{ $r->test->result_route }}" class="text-xs text-indigo-400 hover:text-indigo-300" target="_blank">View →</a>
                        @else<span class="text-surface-600 text-xs">—</span>@endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

</div>
</div>
</x-app-layout>
