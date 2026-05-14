<?php

/*
|--------------------------------------------------------------------------
| Beta Mode
|--------------------------------------------------------------------------
| Single switch driving all beta-period behavior: signup credit grant,
| feedback banner, ?ref= source tracking, and hiding paid-tier CTAs.
|
| Flip BETA_MODE=false in .env (and re-cache config) to revert the app to
| normal live behavior. No code changes required for go-live.
*/

return [

    'enabled' => filter_var(env('BETA_MODE', false), FILTER_VALIDATE_BOOLEAN),

    // Credits granted to a brand-new signup during the beta. Outside beta
    // mode, falls through to config('packages.free.credits').
    'signup_credits' => (int) env('BETA_SIGNUP_CREDITS', 3),

    // Where the "give feedback" link in the banner points. Use a Tally /
    // Google Form / Notion form. Leave empty to disable the link (banner
    // still renders text).
    'feedback_url' => env('BETA_FEEDBACK_URL', ''),

    // Banner copy. Override per-deployment without code changes.
    'banner_text' => env('BETA_BANNER_TEXT', 'You\'re using IELTS Band AI Beta. Found a bug or have feedback?'),

    // Independent toggles, default to mirroring `enabled` so a single env
    // flag is enough to flip the whole experience. Override individually
    // only if you want partial-beta states (e.g. show banner but accept
    // payments).
    'hide_pay_cta' => filter_var(env('BETA_HIDE_PAY_CTA', env('BETA_MODE', false)), FILTER_VALIDATE_BOOLEAN),
    'track_ref' => filter_var(env('BETA_TRACK_REF', env('BETA_MODE', false)), FILTER_VALIDATE_BOOLEAN),

    // Shown in place of the pay button when hide_pay_cta is true.
    'pay_replacement_text' => env('BETA_PAY_REPLACEMENT', 'Pro plans launching soon — leave feedback to get early access'),

    // Hides the "Continue with Google" button on login/register and short-
    // circuits the /auth/google route. Useful when OAuth callback is broken
    // on the beta domain (e.g. invalid_client during onrender.com testing).
    // Defaults to mirroring `enabled` so beta = email-only auth.
    'disable_google_oauth' => filter_var(env('BETA_DISABLE_GOOGLE_OAUTH', env('BETA_MODE', false)), FILTER_VALIDATE_BOOLEAN),
];
