<section>
    <p class="text-surface-400 text-sm mb-5 leading-relaxed">
        Once your account is deleted, all data will be permanently removed. Please download any data you wish to keep before proceeding.
    </p>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="btn-danger"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
        Delete Account
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-xl font-bold text-surface-50 mb-3">Delete your account?</h2>
            <p class="text-surface-400 text-sm mb-6 leading-relaxed">
                This is permanent and cannot be undone. All your tests, results and data will be deleted. Enter your password to confirm.
            </p>

            <div class="mb-6">
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password"
                    placeholder="Enter your password"
                    class="input @if($errors->userDeletion->has('password')) border-red-500 focus:ring-red-500 @endif">
                @if($errors->userDeletion->has('password'))
                    <p class="mt-1.5 text-xs text-red-400">{{ $errors->userDeletion->first('password') }}</p>
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-danger">Delete Account</button>
            </div>
        </form>
    </x-modal>
</section>
