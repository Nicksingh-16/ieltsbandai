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
                
                // Update OAuth info if not already set
                if (!$user->provider) {
                    $user->update([
                        'provider' => 'google',
                        'provider_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                    ]);
                }
            } else {
                \Log::info('Creating new user from Google OAuth');
                
                // Create new user with 3 free credits
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => null, // Explicitly set to null for OAuth users
                    'email_verified_at' => now(), // Auto-verify OAuth emails
                    'test_credits' => 3, // Free credits
                ]);
                
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
