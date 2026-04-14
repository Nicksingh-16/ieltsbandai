<x-app-layout>
    <div class="min-h-screen bg-surface-950 py-10 px-4">
        <div class="max-w-3xl mx-auto space-y-6">

            {{-- Page header --}}
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('dashboard') }}" class="btn-ghost text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Dashboard
                </a>
            </div>

            <div>
                <h1 class="text-2xl font-bold text-surface-50">Profile &amp; Settings</h1>
                <p class="text-surface-400 text-sm mt-1">Manage your account details and subscription.</p>
            </div>

            {{-- Profile overview --}}
            <div class="card p-6 flex flex-col sm:flex-row items-start sm:items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white text-2xl font-bold shadow-glow shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xl font-bold text-surface-50 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-surface-400 text-sm truncate">{{ Auth::user()->email }}</p>
                </div>
                <div class="shrink-0 flex flex-col sm:items-end gap-2">
                    @if(Auth::user()->hasActiveSubscription())
                        <span class="inline-flex items-center gap-1.5 bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs font-semibold px-3 py-1.5 rounded-full">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L9 9H1l6.5 5-2.5 8L12 17l7 5-2.5-8L23 9h-8z"/></svg>
                            Pro Plan
                        </span>
                    @else
                        <span class="text-xs text-surface-500 font-medium">Free Plan</span>
                        <a href="{{ route('pricing') }}" class="btn-primary text-sm px-4 py-2">Upgrade to Pro</a>
                    @endif
                </div>
            </div>

            {{-- Account information --}}
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-surface-700">
                    <h2 class="font-bold text-surface-50">Account Information</h2>
                    <p class="text-surface-500 text-xs mt-0.5">Update your name and email address.</p>
                </div>
                <div class="p-6">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Change password --}}
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-surface-700">
                    <h2 class="font-bold text-surface-50">Change Password</h2>
                    <p class="text-surface-500 text-xs mt-0.5">Use a strong, unique password for your account.</p>
                </div>
                <div class="p-6">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- Danger zone --}}
            <div class="card border-red-500/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-red-500/20">
                    <h2 class="font-bold text-red-400">Delete Account</h2>
                    <p class="text-surface-500 text-xs mt-0.5">Permanently delete your account and all data. This cannot be undone.</p>
                </div>
                <div class="p-6">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
