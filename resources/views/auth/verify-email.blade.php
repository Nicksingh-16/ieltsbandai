<x-guest-layout>
    <div class="space-y-5">

        <div class="text-sm text-gray-700 leading-relaxed">
            We've sent a verification link to
            <span class="font-semibold text-gray-900">{{ auth()->user()?->email ?? 'your email' }}</span>.
            Click the link in that email to activate your account and unlock the full Writing, Speaking, Reading and Listening tests.
        </div>

        {{-- Spam-folder advisory — verification mail is currently sent from a Gmail
             address via Brevo SMTP, which Gmail flags as a SPF/DKIM mismatch and
             often routes to Spam. Until we move sending to a verified domain
             (noreply@vidhyavihar.cloud with proper DNS records), tell users
             upfront so they don't think the email is missing. --}}
        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="text-amber-900">
                    <p class="font-semibold mb-1">Don't see the email?</p>
                    <ul class="list-disc list-inside space-y-0.5 text-amber-800">
                        <li><strong>Check your Spam / Junk folder</strong> — that's where it usually lands the first time.</li>
                        <li>Mark it as "Not Spam" so future emails arrive in your inbox.</li>
                        <li>Add <code class="bg-amber-100 px-1 rounded text-xs">ieltsband25@gmail.com</code> to your contacts.</li>
                        <li>Wait up to 2 minutes — sometimes there's a small delivery delay.</li>
                    </ul>
                </div>
            </div>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                ✓ A new verification link has been sent. Please check your inbox <em>and Spam folder</em>.
            </div>
        @endif

        <div class="flex items-center justify-between pt-2">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>

        <p class="text-xs text-gray-500 text-center pt-2">
            Still no email after 5 minutes? Email <a href="mailto:ronnie@vedcool.com" class="text-blue-600 hover:underline">ronnie@vedcool.com</a> and we'll verify you manually.
        </p>
    </div>
</x-guest-layout>
