<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'deepgram' => [
        'api_key' => env('DEEPGRAM_API_KEY'),
    ],

    'assemblyai' => [
        'api_key' => env('ASSEMBLYAI_API_KEY'),
    ],

    'transcription' => [
        'provider' => env('TRANSCRIPTION_PROVIDER', 'deepgram'), // 'deepgram' (primary — faster + cheaper, handles WebM/Opus reliably) or 'assemblyai' (fallback). Order is enforced in TranscriptionService::transcribeWithWords().
    ],

    // OpenAI is the optional primary tier in LLMRouter. The router gates this
    // tier on the key starting with 'sk-' — so a placeholder or a misused
    // Gemini key won't accidentally route real traffic here. Defaults point
    // at the real OpenAI endpoint; set OPENAI_API_KEY=sk-... in .env to enable.
    'openai' => [
        'api_key'  => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1/'),
        'model'    => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    // OpenRouter — paid passthrough to OpenAI/Anthropic/etc., paid via UPI.
    // Sits in front of the openai tier in LLMRouter; gated on key starting
    // with 'sk-or-' to avoid accidental activation. Daily/total USD caps
    // give a second line of defence on top of OpenRouter's own per-key cap.
    'openrouter' => [
        'api_key'  => env('OPENROUTER_API_KEY'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1/'),
        'model'    => env('OPENROUTER_MODEL', 'openai/gpt-4o-mini'),
        // Premium subscribers (Pro Plus / model_tier=premium) route here.
        // ~17x cost of mini, materially better Band 7+ accuracy.
        'premium_model' => env('OPENROUTER_PREMIUM_MODEL', 'openai/gpt-4o'),
        'daily_usd_cap' => env('LLM_OPENROUTER_DAILY_USD_CAP'),
        'total_usd_cap' => env('LLM_OPENROUTER_TOTAL_USD_CAP'),
    ],

    'gemini' => [
        // 5-key pool for round-robin and Pro→Flash fallback (see LLMRouter).
        // array_filter strips empty slots so the router can be configured with
        // 1–5 keys without code changes.
        'keys' => array_values(array_filter([
            env('GEMINI_API_KEY_1'),
            env('GEMINI_API_KEY_2'),
            env('GEMINI_API_KEY_3'),
            env('GEMINI_API_KEY_4'),
            env('GEMINI_API_KEY_5'),
        ])),
        // Primary (used first by the Gemini provider) and fallback (tried on
        // quota / rate-limit failure). Distinct by default so the "retry with
        // fallback model" branch in LLMRouter is meaningful out of the box.
        'primary_model'  => env('GEMINI_PRIMARY_MODEL', 'gemini-2.5-pro'),
        'fallback_model' => env('GEMINI_FALLBACK_MODEL', 'gemini-2.5-flash'),
        'base_url'       => env('OPENAI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/openai/'),

        // Legacy single-key var (comma-separated) — preserved so existing
        // GeminiService.php helpers that still read it keep working.
        'api_keys' => env('GEMINI_API_KEYS', ''),
    ],

    // Groq is the active PRIMARY provider as of L4. Free tier: 14,400 req/day,
    // Llama 3.3 70B Versatile model. OpenAI-compatible endpoint, no card.
    // LLMRouter tries Groq first, then falls back to Gemini Flash on quota
    // exhaustion or any non-2xx that isn't a true content failure.
    'groq' => [
        'api_key'  => env('GROQ_API_KEY'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1/'),
        'model'    => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    ],

    'calibration' => [
        // Toggle the Layer 3 few-shot block in writing prompts. Set to false
        // to capture a no-calibration baseline benchmark for A/B comparison.
        'few_shot_enabled' => env('FEW_SHOT_ENABLED', true),
        // Toggle post-LLM piecewise upward bias correction (see
        // ScoringService::calibrateScore Stage 2). Disable to A/B without it.
        'bias_correction_enabled' => env('BIAS_CORRECTION_ENABLED', true),
    ],

    'languagetool' => [
        // Self-hosted LanguageTool via docker compose up -d languagetool.
        // Falls back to "n/a" in prompts if unreachable.
        'base_url' => env('LANGUAGETOOL_BASE_URL', 'http://localhost:8010'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

    'razorpay' => [
        'key'            => env('RAZORPAY_KEY'),
        'secret'         => env('RAZORPAY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

    // Manual UPI payment config — used while Razorpay isn't activated (no
    // domain yet). The paywall renders a UPI deep-link QR pointing at this
    // VPA; users pay, submit their UTR, we grant credits/sub immediately and
    // verify the UTR against the bank statement out-of-band.
    'upi' => [
        'vpa'      => env('UPI_VPA', 'ronnie@ybl'),
        'name'     => env('UPI_NAME', 'IELTS Band AI'),
        // Optional: notification email when a new manual payment lands.
        'notify_email' => env('UPI_NOTIFY_EMAIL'),
    ],

];
