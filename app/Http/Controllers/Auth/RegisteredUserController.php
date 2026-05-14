<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\ReferralController;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // `indisposable` rule (propaganistas/laravel-disposable-email) blocks
            // mailinator, tempmail, 10minutemail, etc. — primary defence against
            // signup farming for free test_credits + self_eval_credits.
            'email' => ['required', 'string', 'lowercase', 'email', 'indisposable', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // test_credits is intentionally NOT mass-assignable on User (a user
        // could otherwise submit `test_credits=999` in the signup form). Set
        // via forceFill inside this trusted controller.
        $signupCredits = config('beta.enabled')
            ? config('beta.signup_credits')
            : config('packages.free.credits', 3);

        $user = new User;
        $user->forceFill([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'test_credits' => $signupCredits,
            'self_eval_credits' => 1,        // separate pool for the /evaluate page
            'ref_source' => $request->session()->pull('ref_source'),
        ])->save();

        event(new Registered($user));

        Auth::login($user);

        // Apply referral bonus if user came via referral link
        ReferralController::applyReferral($user);

        // First-party analytics — track signup with attribution.
        app(\App\Services\EventTracker::class)->track('user_signed_up', [
            'ref_source' => $user->ref_source,
            'referred_by' => $user->referred_by,
            'beta' => (bool) config('beta.enabled'),
        ], $user);

        // Send welcome email (best-effort — never let mail failure break signup).
        // Resend in test mode rejects mail to unverified domains; queue is sync
        // by default so the exception would propagate to a 500.
        try {
            Mail::to($user)->queue(new WelcomeMail($user));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Welcome mail failed (non-fatal)', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect(route('dashboard', absolute: false));
    }
}
