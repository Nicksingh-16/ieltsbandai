<section>
    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="label">Current Password</label>
            <input id="update_password_current_password" name="current_password" type="password"
                autocomplete="current-password"
                class="input @error('current_password', 'updatePassword') border-red-500 focus:ring-red-500 @enderror">
            @error('current_password', 'updatePassword')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="label">New Password</label>
            <input id="update_password_password" name="password" type="password"
                autocomplete="new-password"
                class="input @error('password', 'updatePassword') border-red-500 focus:ring-red-500 @enderror">
            @error('password', 'updatePassword')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="label">Confirm Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                autocomplete="new-password" class="input">
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button type="submit" class="btn-primary">Update Password</button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-emerald-400 font-medium">Saved.</p>
            @endif
        </div>
    </form>
</section>
