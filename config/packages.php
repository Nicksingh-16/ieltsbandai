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

    'monthly' => [
        'name' => 'Monthly Pro',
        'credits' => 50,
        'price' => 299, // in rupees
        'duration_days' => 30,
        'features' => [
            '50 tests per month',
            'Advanced AI analysis',
            'Band 9 model answers',
            'Real-time writing analyzer',
            'Filler word detection',
            'Priority support',
        ],
    ],

    'yearly' => [
        'name' => 'Yearly Pro',
        'credits' => 600,
        'price' => 2999, // in rupees (save ₹589)
        'duration_days' => 365,
        'features' => [
            '600 tests per year',
            'All Monthly Pro features',
            'Exclusive study materials',
            'Personalized coaching tips',
            'Lifetime access to updates',
        ],
        'badge' => 'Best Value',
    ],

    // ── Institute (B2B) Plans ─────────────────────────────────────────────────
    'institute' => [
        'starter' => [
            'name'       => 'Starter Institute',
            'price'      => 2999,           // ₹2,999 / month
            'seat_limit' => 30,
            'duration_days' => 30,
            'features'   => [
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
            'name'       => 'Pro Institute',
            'price'      => 5999,           // ₹5,999 / month
            'seat_limit' => 100,
            'duration_days' => 30,
            'features'   => [
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
            'name'       => 'Enterprise',
            'price'      => 12999,          // ₹12,999 / month
            'seat_limit' => 500,
            'duration_days' => 30,
            'features'   => [
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
