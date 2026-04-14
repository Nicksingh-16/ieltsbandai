<x-app-layout>
<div class="min-h-screen bg-surface-950">
<div class="max-w-7xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-surface-50">Assignments</h1>
            <p class="text-surface-400 text-sm mt-1">{{ $institute->name }} · tests assigned to batches</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('institute.dashboard') }}" class="btn-secondary text-sm">← Dashboard</a>
            @if(Auth::user()->isTeacher())
            <a href="{{ route('institute.assignments.create') }}" class="btn-primary text-sm">+ Assign Test</a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-lg px-4 py-3 text-emerald-400 text-sm mb-6">{{ session('success') }}</div>
    @endif

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full min-w-[600px] text-sm">
            <thead>
                <tr class="border-b border-surface-800">
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Title</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Batch</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Type</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Due</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Progress</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Status</th>
                    <th class="text-left px-4 py-3 text-surface-400 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface-800">
                @forelse($assignments as $a)
                <tr class="hover:bg-surface-900">
                    <td class="px-4 py-3">
                        <a href="{{ route('institute.assignments.show', $a) }}" class="text-surface-100 hover:text-indigo-400 font-medium">{{ $a->title }}</a>
                        @if($a->is_mandatory)
                        <span class="ml-2 text-xs bg-red-500/20 text-red-400 px-1.5 py-0.5 rounded">Mandatory</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-surface-300 text-xs">{{ optional($a->batch)->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs bg-surface-800 text-surface-300 px-2 py-0.5 rounded">{{ str_replace('_',' ', $a->template->type ?? '—') }}</span>
                    </td>
                    <td class="px-4 py-3 text-surface-400 text-xs">
                        @if($a->due_date)
                            <span class="{{ $a->isOverdue() ? 'text-red-400' : '' }}">{{ $a->due_date->format('d M Y') }}</span>
                        @else—@endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-surface-800 rounded-full h-1.5 w-20">
                                <div class="bg-indigo-500 h-1.5 rounded-full" style="width:{{ $a->student_records_count ? round(($a->completed_count/$a->student_records_count)*100) : 0 }}%"></div>
                            </div>
                            <span class="text-xs text-surface-400">{{ $a->completed_count }}/{{ $a->student_records_count }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if($a->status === 'active')
                            <span class="text-xs bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full">Active</span>
                        @elseif($a->status === 'closed')
                            <span class="text-xs bg-surface-800 text-surface-500 px-2 py-0.5 rounded-full">Closed</span>
                        @else
                            <span class="text-xs bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded-full">Draft</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 flex items-center gap-3">
                        <a href="{{ route('institute.assignments.show', $a) }}" class="text-xs text-indigo-400 hover:text-indigo-300">View</a>
                        @if(Auth::user()->isTeacher())
                        <form method="POST" action="{{ route('institute.assignments.toggle', $a) }}">
                            @csrf
                            <button class="text-xs text-surface-400 hover:text-surface-200">
                                {{ $a->status === 'active' ? 'Close' : 'Reopen' }}
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-surface-500">No assignments yet. <a href="{{ route('institute.assignments.create') }}" class="text-indigo-400">Create one →</a></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <div class="mt-4">{{ $assignments->links() }}</div>

</div>
</div>
</x-app-layout>
