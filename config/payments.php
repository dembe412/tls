<?php

return [
    'methods' => [
        'airtel' => [
            'key' => 'airtel',
            'name' => 'Airtel Money',
            'number' => env('PAYMENT_AIRTEL_NUMBER', '0750000000'),
            'account_name' => env('PAYMENT_AIRTEL_NAME', 'TSL Smart Locks'),
        ],
        'mtn' => [
            'key' => 'mtn',
            'name' => 'MTN Mobile Money',
            'number' => env('PAYMENT_MTN_NUMBER', '0770000000'),
            'account_name' => env('PAYMENT_MTN_NAME', 'TSL Smart Locks'),
        ],
    ],
];
