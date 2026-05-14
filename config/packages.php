<?php

return [
    'free' => [
        'name' => 'Free Plan',
        'credits' => 3,
        'price' => 0,
        'duration_days' => 0,
        'features' => [
            '3 tests total',
            'Basic AI feedback',
            'Band score breakdown',
        ],
    ],

    // Consumer subscription plans (Pro Monthly ₹99, Pro Plus ₹199) live in
    // config/plans.php as the single source of truth. Legacy monthly/yearly
    // plans were removed — the new paywall view reads from config/plans.php.

    // ── Institute (B2B) Plans ─────────────────────────────────────────────────
    'institute' => [
        'starter' => [
            'name' => 'Starter Institute',
            'price' => 2999,           // ₹2,999 / month
            'seat_limit' => 30,
            'duration_days' => 30,
            'features' => [
                'Up to 30 students',
                'All 4 IELTS modules',
                'Batch management',
                'Question bank (50 questions)',
                'Assignment & tracking',
                'Batch analytics dashboard',
                'Email support',
            ],
            'badge' => null,
        ],
        'pro' => [
            'name' => 'Pro Institute',
            'price' => 5999,           // ₹5,999 / month
            'seat_limit' => 100,
            'duration_days' => 30,
            'features' => [
                'Up to 100 students',
                'All Starter features',
                'Unlimited question bank',
                'Custom assignments & mock tests',
                'Student progress delta tracking',
                'PDF score reports for students',
                'Priority support + onboarding call',
            ],
            'badge' => 'Most Popular',
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'price' => 12999,          // ₹12,999 / month
            'seat_limit' => 500,
            'duration_days' => 30,
            'features' => [
                'Up to 500 students',
                'All Pro features',
                'Dedicated account manager',
                'Custom branding (coming soon)',
                'API access',
                'SLA guarantee',
                'Quarterly performance reviews',
            ],
            'badge' => 'Enterprise',
        ],
    ],
];
