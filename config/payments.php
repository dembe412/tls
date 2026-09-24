<?php

return [
    'min_withdraw' => (int) env('MIN_WITHDRAW_UGX', 2000),

    'methods' => [
        'airtel' => [
            'key' => 'airtel',
            'name' => 'Airtel Money',
            'number' => '0750000000',
            'account_name' => 'TSL Smart Locks',
        ],
        'mtn' => [
            'key' => 'mtn',
            'name' => 'MTN Mobile Money',
            'number' => '0770000000',
            'account_name' => 'TSL Smart Locks',
        ],
    ],
];
