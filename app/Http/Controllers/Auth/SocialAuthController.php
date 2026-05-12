<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        if (config('beta.disable_google_oauth')) {
            return redirect()->route('login')
                ->with('error', 'Google sign-in is temporarily disabled during beta. Please use email and password.');
        }
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            \Log::info('Google OAuth callback received', [
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
            ]);
            
            // Check if user exists by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                \Log::info('Existing user found, updating OAuth info');
                
                // Update OAuth info if not already set. provider/provider_id/
                // avatar/email_verified_at are intentionally NOT in User::$fillable
                // — use forceFill inside this trusted OAuth callback.
                if (!$user->provider) {
                    $user->forceFill([
                        'provider'    => 'google',
                        'provider_id' => $googleUser->getId(),
                        'avatar'      => $googleUser->getAvatar(),
                    ])->save();
                }
            } else {
                \Log::info('Creating new user from Google OAuth');
                
                // Create new user. Credit amount comes from config: beta mode
                // grants config('beta.signup_credits'); otherwise the free-plan
                // default. Switch with BETA_MODE in .env, no code changes.
                $signupCredits = config('beta.enabled')
                    ? config('beta.signup_credits')
                    : config('packages.free.credits', 3);

                $user = new User();
                $user->forceFill([
                    'name'              => $googleUser->getName(),
                    'email'             => $googleUser->getEmail(),
                    'provider'          => 'google',
                    'provider_id'       => $googleUser->getId(),
                    'avatar'            => $googleUser->getAvatar(),
                    'password'          => null,                       // OAuth users have no local password
                    'email_verified_at' => now(),                      // Google guarantees email ownership
                    'test_credits'      => $signupCredits,
                    'ref_source'        => session()->pull('ref_source'),
                ])->save();
                
                \Log::info('New user created successfully', ['user_id' => $user->id]);
            }

            // Log the user in
            Auth::login($user, true);
            
            \Log::info('User logged in successfully', ['user_id' => $user->id]);

            return redirect()->route('dashboard')
                ->with('success', 'Welcome back, ' . $user->name . '!');

        } catch (Exception $e) {
            \Log::error('Google OAuth failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Unable to login with Google. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Redirect to Facebook OAuth (placeholder for future)
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Handle Facebook OAuth callback (placeholder for future)
     */
    public function handleFacebookCallback()
    {
        // Similar implementation to Google
        return redirect()->route('login')
            ->with('info', 'Facebook login coming soon!');
    }
}
