<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    const REFERRER_REWARD  = 2; // credits given to referrer when referral completes first test
    const REFEREE_BONUS    = 1; // extra credits given to new user on signup via referral

    public function show()
    {
        $user = Auth::user();

        if (!$user->referral_code) {
            $user->referral_code = strtoupper(Str::random(8));
            $user->save();
        }

        $referrals = User::where('referred_by', $user->id)
            ->select('id', 'name', 'created_at', 'test_credits')
            ->latest()
            ->get();

        $referralUrl = route('referral.track', $user->referral_code);

        return view('pages.referral.show', compact('user', 'referrals', 'referralUrl'));
    }

    // Called when someone visits /ref/{code} — stores code in session, redirects to register
    public function track(string $code)
    {
        $referrer = User::where('referral_code', $code)->first();

        if ($referrer && (!Auth::check() || Auth::id() !== $referrer->id)) {
            session(['referral_code' => $code]);
        }

        return redirect()->route('register');
    }

    // Called after registration. Records the referral link and gives the
    // referee their signup bonus, but defers the referrer reward until the
    // referee actually completes a test (anti-farming — see creditReferrer).
    public static function applyReferral(User $newUser): void
    {
        $code = session('referral_code');
        if (!$code) return;

        $referrer = User::where('referral_code', $code)->first();
        if (!$referrer || $referrer->id === $newUser->id) return;

        // Same email-domain detection: extra defence — block obvious farms
        // where attacker chains throwaway addresses on a single domain. Skip
        // bonus + reward entirely; user still registers.
        if (self::looksLikeSelfReferral($newUser, $referrer)) {
            session()->forget('referral_code');
            return;
        }

        // Small bonus for the referee on signup (capped at +1).
        $newUser->increment('test_credits', self::REFEREE_BONUS);
        $newUser->update(['referred_by' => $referrer->id]);

        // Referrer is NOT credited yet. Their reward fires from
        // creditReferrer() once the referee finishes a real test.

        session()->forget('referral_code');
    }

    /**
     * Award the referrer's bonus when the referee completes their first
     * test. Idempotent via referral_credited_at — a user can only ever
     * trigger the referrer reward once.
     */
    public static function creditReferrer(User $referee): void
    {
        if (!$referee->referred_by || $referee->referral_credited_at) {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($referee) {
            // Lock the referee row so two parallel test completions can't
            // both fire the reward.
            $locked = User::whereKey($referee->id)->lockForUpdate()->first();
            if (!$locked || !$locked->referred_by || $locked->referral_credited_at) {
                return;
            }

            $referrer = User::whereKey($locked->referred_by)->lockForUpdate()->first();
            if (!$referrer) {
                $locked->forceFill(['referral_credited_at' => now()])->save();
                return;
            }

            // Cap referrer's lifetime farmed credits to discourage chained
            // throwaway accounts. Past the cap, mark credited but skip debit.
            $cap = (int) config('referrals.max_credits_per_referrer', 50);
            if ($referrer->referral_credits_earned < $cap) {
                $referrer->increment('test_credits', self::REFERRER_REWARD);
                $referrer->increment('referral_credits_earned', self::REFERRER_REWARD);
            }

            $locked->forceFill(['referral_credited_at' => now()])->save();
        });
    }

    private static function looksLikeSelfReferral(User $newUser, User $referrer): bool
    {
        // Strip plus-addressing and compare local-part roots — defeats
        // alice+1@gmail / alice+2@gmail farming on the same inbox.
        $normalize = function (string $email): string {
            [$local, $domain] = array_pad(explode('@', strtolower($email), 2), 2, '');
            $local = preg_replace('/\+.*$/', '', $local);
            $local = $domain === 'gmail.com' ? str_replace('.', '', $local) : $local;
            return $local . '@' . $domain;
        };

        return $normalize($newUser->email) === $normalize($referrer->email);
    }
}
