<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-surface-50">Create your account</h2>
        <p class="text-surface-400 text-sm mt-1">Free forever. 3 credits on signup. No card required.</p>
    </div>

    {{-- Benefits strip --}}
    <div class="flex items-center gap-4 mb-6 p-3 bg-brand-500/10 border border-brand-500/25 rounded-xl">
        <div class="flex gap-3 flex-wrap text-xs text-brand-300 font-medium">
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Instant AI feedback
            </span>
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                All 4 IELTS skills
            </span>
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Band score in seconds
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        {{-- Name --}}
        <div>
            <x-input-label for="name" :value="__('Full Name')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')"
                required autofocus autocomplete="name" placeholder="Your full name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')"
                required autocomplete="username" placeholder="you@email.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        {{-- Password --}}
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password"
                required autocomplete="new-password" placeholder="Min. 8 characters" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        {{-- Confirm Password --}}
        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation"
                required autocomplete="new-password" placeholder="Re-enter password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        {{-- Terms --}}
        <p class="text-xs text-surface-500">
            By creating an account you agree to our
            <a href="#" class="text-brand-400 hover:text-brand-300">Terms</a> and
            <a href="#" class="text-brand-400 hover:text-brand-300">Privacy Policy</a>.
        </p>

        {{-- Submit --}}
        <x-primary-button class="w-full justify-center py-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Create Account — It's Free
        </x-primary-button>
    </form>

    {{-- Divider --}}
    <div class="relative my-5">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-surface-600"></div>
        </div>
        <div class="relative flex justify-center">
            <span class="px-3 bg-surface-800 text-xs text-surface-500">or sign up with</span>
        </div>
    </div>

    {{-- Google --}}
    <a href="{{ route('auth.google') }}"
       class="flex items-center justify-center gap-3 w-full px-4 py-2.5 bg-surface-700 hover:bg-surface-600 border border-surface-600 rounded-xl text-sm font-medium text-surface-200 transition-colors">
        <svg class="w-4 h-4" viewBox="0 0 24 24">
            <path fill="#EA4335" d="M5.266 9.765A7.077 7.077 0 0 1 12 4.909c1.69 0 3.218.6 4.418 1.582L19.91 3C17.782 1.145 15.055 0 12 0 7.27 0 3.198 2.698 1.24 6.65l4.026 3.115Z"/>
            <path fill="#34A853" d="M16.04 18.013c-1.09.703-2.474 1.078-4.04 1.078a7.077 7.077 0 0 1-6.723-4.823l-4.04 3.067A11.965 11.965 0 0 0 12 24c2.933 0 5.735-1.043 7.834-3l-3.793-2.987Z"/>
            <path fill="#4A90E2" d="M19.834 21c2.195-2.048 3.62-5.096 3.62-9 0-.71-.109-1.473-.272-2.182H12v4.637h6.436c-.317 1.559-1.17 2.766-2.395 3.558L19.834 21Z"/>
            <path fill="#FBBC05" d="M5.277 14.268A7.12 7.12 0 0 1 4.909 12c0-.782.125-1.533.357-2.235L1.24 6.65A11.934 11.934 0 0 0 0 12c0 1.92.445 3.73 1.237 5.335l4.04-3.067Z"/>
        </svg>
        Continue with Google
    </a>

    {{-- Login link --}}
    <p class="text-center text-sm text-surface-400 mt-5">
        Already have an account?
        <a href="{{ route('login') }}" class="text-brand-400 hover:text-brand-300 font-medium transition-colors">
            Sign in
        </a>
    </p>
</x-guest-layout>
