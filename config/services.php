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
        'provider' => env('TRANSCRIPTION_PROVIDER', 'assemblyai'), // 'deepgram' or 'assemblyai' (assemblyai handles WebM better)
    ],

    // The 'openai' key is a misnomer in this codebase: the active LLM provider
    // is Gemini via its OpenAI-compatible endpoint. Defaults below point at
    // Gemini so a fresh checkout boots with the free provider. To migrate to
    // the real OpenAI later, set OPENAI_BASE_URL and OPENAI_MODEL in .env —
    // no code changes required.
    'openai' => [
        'api_key'  => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/openai/'),
        'model'    => env('OPENAI_MODEL', 'gemini-2.5-pro'),
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
        'primary_model'  => env('GEMINI_PRIMARY_MODEL', 'gemini-2.5-flash'),
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

];
