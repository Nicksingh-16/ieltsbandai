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

    // Called after registration to credit both parties — used in RegisteredUserController
    public static function applyReferral(User $newUser): void
    {
        $code = session('referral_code');
        if (!$code) return;

        $referrer = User::where('referral_code', $code)->first();
        if (!$referrer || $referrer->id === $newUser->id) return;

        // Bonus credit for the new user
        $newUser->increment('test_credits', self::REFEREE_BONUS);
        $newUser->update(['referred_by' => $referrer->id]);

        // Reward for the referrer
        $referrer->increment('test_credits', self::REFERRER_REWARD);
        $referrer->increment('referral_credits_earned', self::REFERRER_REWARD);

        session()->forget('referral_code');
    }
}
