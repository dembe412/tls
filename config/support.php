<?php

return [
    'whatsapp' => env('SUPPORT_WHATSAPP', ''),

    /*
     * Percentage of a purchase price paid to the people above the buyer.
     * Level A is the member who invited the buyer, B invited A, C invited B.
     */
    'referral_rates' => [
        'A' => ['lock' => 5, 'vip' => 10],
        'B' => ['lock' => 2, 'vip' => 2],
        'C' => ['lock' => 1, 'vip' => 1],
    ],

    /* Seconds a manager's bonus code stays claimable. */
    'bonus_code_ttl' => (int) env('BONUS_CODE_TTL', 1800),
];
