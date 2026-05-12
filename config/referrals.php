<?php

return [
    // Lifetime cap on referrer rewards (in credits). Past this, no more
    // reward fires regardless of how many referees complete tests. Stops
    // chained throwaway-account farming from minting unlimited credits.
    'max_credits_per_referrer' => (int) env('REFERRAL_MAX_CREDITS', 50),
];
