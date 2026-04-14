<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="label">Full Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                required autofocus autocomplete="name"
                class="input @error('name') border-red-500 focus:ring-red-500 @enderror">
            @error('name')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="label">Email Address</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                required autocomplete="username"
                class="input @error('email') border-red-500 focus:ring-red-500 @enderror">
            @error('email')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 bg-amber-500/10 border border-amber-500/25 rounded-xl p-4">
                    <p class="text-sm text-amber-300 mb-2">Your email address is unverified.</p>
                    <button form="send-verification" class="text-sm font-medium text-brand-400 hover:text-brand-300 underline transition-colors">
                        Re-send verification email
                    </button>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-xs text-emerald-400 font-medium">Verification link sent!</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button type="submit" class="btn-primary">Save Changes</button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-emerald-400 font-medium">Saved.</p>
            @endif
        </div>
    </form>
</section>
