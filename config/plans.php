<?php

/**
 * Pricing & plan definitions.
 *
 * Single source of truth for what the paywall sells, what each plan grants,
 * and how to render its conversion psychology (badges, savings, anchors).
 *
 * Plan keys are stable identifiers persisted on payments.plan — DO NOT rename
 * without a data migration. Add new plans at the end.
 */

return [

    'currency'        => 'INR',
    'currency_symbol' => '₹',

    /*
    |--------------------------------------------------------------------------
    | One-time purchases — pay-per-test
    |--------------------------------------------------------------------------
    |
    | "credits" are added to users.test_credits. They're generic across test
    | types — buying a single_writing grants 1 credit usable for any test type.
    | We accept this minor pricing-arbitrage for beta simplicity; revisit when
    | enough users misuse it to matter.
    |
    */
    'one_time' => [
        'single_writing' => [
            'label'        => 'Writing Practice',
            'subtitle'     => '1 Writing test with full AI evaluation',
            'price'        => 9,
            'credits'      => 1,
            'duration_days'=> null,
            'tier'         => 'standard',
            'features'     => [
                'Detailed band score (TA, CC, LR, GRA)',
                'Error highlighting + Band-9 model rewrite',
                'Calibrated against Cambridge IELTS',
            ],
            'badge'        => null,
        ],

        'single_speaking' => [
            'label'        => 'Speaking Practice',
            'subtitle'     => '1 Speaking test (Parts 1–3) with transcript',
            'price'        => 12,
            'credits'      => 1,
            'duration_days'=> null,
            'tier'         => 'standard',
            'features'     => [
                'Full Parts 1, 2, 3 simulation',
                'Fluency + pronunciation + lexical analysis',
                'Word-level transcript with pause/filler counts',
            ],
            'badge'        => null,
        ],

        'single_full' => [
            'label'        => 'Full Mock Test',
            'subtitle'     => 'Writing + Speaking + Listening + Reading',
            'price'        => 20,
            'credits'      => 4,
            'duration_days'=> null,
            'tier'         => 'standard',
            'features'     => [
                'All four IELTS modules in one session',
                'Combined band-score report',
                'Saves ₹16 vs buying individually',
            ],
            'badge'        => 'Save 44%',
        ],

        // Self-evaluation pack — for users who want to evaluate their own
        // Task 2 essays without taking a fresh test. Grants 1 self_eval credit
        // (separate pool from test_credits). Cheap because the only AI cost
        // is one writing evaluation pass.
        'self_eval_single' => [
            'label'        => 'Essay Evaluation',
            'subtitle'     => 'Evaluate one Task 2 essay you wrote elsewhere',
            'price'        => 10,
            'credits'      => 1,
            'credits_pool' => 'self_eval',
            'duration_days'=> null,
            'tier'         => 'standard',
            'features'     => [
                'Full IELTS-style band-score breakdown',
                'Error highlighting + corrections',
                'Band-9 model rewrite of your prompt',
            ],
            'badge'        => null,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscriptions — monthly recurring (renewal is manual UPI for beta)
    |--------------------------------------------------------------------------
    |
    | Granting a subscription writes a row to the subscriptions table with
    | status='active' + ends_at=now()+duration_days. CreditService::isPro
    | reads that as the source of truth for unlimited-tests access.
    |
    | model_tier flows into LLMRouter::chatCompletion — 'premium' uses gpt-4o,
    | 'standard' uses gpt-4o-mini.
    |
    */
    'subscription' => [
        'monthly_basic' => [
            'label'        => 'Pro Monthly',
            'subtitle'     => 'Unlimited tests for 30 days',
            'price'        => 99,
            'credits'      => null,           // unlimited via subscription
            'duration_days'=> 30,
            'tier'         => 'standard',
            'model_tier'   => 'standard',     // gpt-4o-mini scoring
            'features'     => [
                'Unlimited Writing + Speaking + Listening + Reading',
                'Full AI feedback on every test',
                'Cancel anytime — no auto-renewal in beta',
                'Beta price — locks in for first 100 users',
            ],
            'badge'        => 'Most Popular',
            'anchor_text'  => 'vs ₹9–20 per test',
        ],

        'monthly_premium' => [
            'label'        => 'Pro Plus Monthly',
            'subtitle'     => 'Unlimited + premium AI accuracy',
            'price'        => 199,
            'credits'      => null,
            'duration_days'=> 30,
            'tier'         => 'premium',
            'model_tier'   => 'premium',       // gpt-4o full scoring (~17x cost, ~2x accuracy on Band 7+)
            'features'     => [
                'Everything in Pro Monthly',
                'Premium AI model (GPT-4o full, not mini)',
                'Higher accuracy on Band 7+ writing',
                'Priority queue for evaluations',
                'Beta price — locks in for first 100 users',
            ],
            'badge'        => 'Best Accuracy',
            'anchor_text'  => 'For serious 7+ aspirants',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Beta launch conversion levers
    |--------------------------------------------------------------------------
    |
    | These knobs drive the psychology copy in the paywall view. Tweak without
    | touching templates.
    |
    */
    'beta' => [
        'cap_users'       => 100,                    // "First 100 users get..."
        'price_locks_in'  => true,
        'social_proof'    => [
            // Updated weekly. Beta-honest: use the actual number from User::count()
            // when comfortable, hardcode here while early.
            'users_joined' => 80,
            'tests_taken'  => 240,
        ],
        'guarantee_days'  => 7,
    ],

];
