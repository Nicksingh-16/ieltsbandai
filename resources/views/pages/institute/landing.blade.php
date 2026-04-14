<x-app-layout>
<div class="min-h-screen bg-surface-950 py-16 px-4">
<div class="max-w-2xl mx-auto">

    <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/20 rounded-full px-4 py-1.5 text-indigo-400 text-sm mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            For Coaching Institutes
        </div>
        <h1 class="text-3xl font-bold text-surface-50 mb-4">Register Your Institute</h1>
        <p class="text-surface-400">Manage all your IELTS students from one dashboard. Track scores, assign batches, and generate progress reports.</p>
    </div>

    {{-- Features --}}
    <div class="grid grid-cols-2 gap-4 mb-10">
        @foreach([
            ['icon'=>'👥','title'=>'Batch Management','desc'=>'Organise students into batches by target band or exam date'],
            ['icon'=>'📊','title'=>'Score Tracking','desc'=>'View all student scores across Listening, Reading, Writing, Speaking'],
            ['icon'=>'📧','title'=>'Student Invites','desc'=>'Invite students by email or bulk CSV — accounts created automatically'],
            ['icon'=>'📄','title'=>'Progress Reports','desc'=>'Track improvement trends and identify weak areas per student'],
        ] as $f)
        <div class="card p-4">
            <div class="text-2xl mb-2">{{ $f['icon'] }}</div>
            <div class="font-semibold text-surface-200 text-sm mb-1">{{ $f['title'] }}</div>
            <div class="text-surface-400 text-xs">{{ $f['desc'] }}</div>
        </div>
        @endforeach
    </div>

    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-3 text-red-400 text-sm mb-6">{{ session('error') }}</div>
    @endif

    {{-- Registration Form --}}
    <form method="POST" action="{{ route('institute.register') }}" class="card p-8 space-y-5">
        @csrf
        <h2 class="text-lg font-semibold text-surface-100">Institute Details</h2>

        <div>
            <label class="block text-sm text-surface-400 mb-1">Institute Name <span class="text-red-400">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                class="w-full bg-surface-900 border border-surface-700 rounded-lg px-4 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500"
                placeholder="e.g. Brilliant IELTS Academy">
            @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm text-surface-400 mb-1">Contact Email <span class="text-red-400">*</span></label>
            <input type="email" name="contact_email" value="{{ old('contact_email', auth()->user()->email) }}" required
                class="w-full bg-surface-900 border border-surface-700 rounded-lg px-4 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500">
            @error('contact_email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-surface-400 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-4 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500"
                    placeholder="+91 98765 43210">
            </div>
            <div>
                <label class="block text-sm text-surface-400 mb-1">City</label>
                <input type="text" name="city" value="{{ old('city') }}"
                    class="w-full bg-surface-900 border border-surface-700 rounded-lg px-4 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500"
                    placeholder="Mumbai">
            </div>
        </div>

        <div>
            <label class="block text-sm text-surface-400 mb-1">GST Number <span class="text-surface-600">(optional — for invoices)</span></label>
            <input type="text" name="gst_number" value="{{ old('gst_number') }}"
                class="w-full bg-surface-900 border border-surface-700 rounded-lg px-4 py-2.5 text-surface-100 text-sm focus:outline-none focus:border-indigo-500"
                placeholder="27AAAAA0000A1Z5">
        </div>

        <p class="text-xs text-surface-500">Free plan: 10 student seats. Upgrade anytime to add more.</p>

        <button type="submit" class="btn-primary w-full py-3 text-base font-bold justify-center">
            Register Institute →
        </button>
    </form>

</div>
</div>
</x-app-layout>
