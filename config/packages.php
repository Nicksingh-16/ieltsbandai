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
];
