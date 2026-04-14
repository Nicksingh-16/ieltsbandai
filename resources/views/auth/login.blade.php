<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-surface-50">Welcome back</h2>
        <p class="text-surface-400 text-sm mt-1">Sign in to continue your IELTS practice.</p>
    </div>

    {{-- Session Status --}}
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')"
                required autofocus autocomplete="username" placeholder="you@email.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <x-input-label for="password" :value="__('Password')" class="mb-0" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-brand-400 hover:text-brand-300 transition-colors">
                        Forgot password?
                    </a>
                @endif
            </div>
            <x-text-input id="password" type="password" name="password"
                required autocomplete="current-password" placeholder="Your password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        {{-- Remember me --}}
        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                class="w-4 h-4 rounded bg-surface-900 border-surface-600 text-brand-500 focus:ring-brand-500 focus:ring-offset-surface-800 cursor-pointer">
            <label for="remember_me" class="text-sm text-surface-400 cursor-pointer select-none">Remember me</label>
        </div>

        {{-- Submit --}}
        <x-primary-button class="w-full justify-center py-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            Sign In
        </x-primary-button>
    </form>

    {{-- Divider --}}
    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-surface-600"></div>
        </div>
        <div class="relative flex justify-center">
            <span class="px-3 bg-surface-800 text-xs text-surface-500">or continue with</span>
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

    {{-- Register link --}}
    <p class="text-center text-sm text-surface-400 mt-6">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-brand-400 hover:text-brand-300 font-medium transition-colors">
            Sign up free
        </a>
    </p>
</x-guest-layout>
