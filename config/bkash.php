<?php

return [
    'app_key' => env('BKASH_APP_KEY'),
    'app_secret' => env('BKASH_APP_SECRET'),
    'username' => env('BKASH_USERNAME'),
    'password' => env('BKASH_PASSWORD'),
    'url' => env('BKASH_URL'),
    'old_username' => env('BKASH_VERIFICATION_USERNAME'),
    'old_password' => env('BKASH_VERIFICATION_PASSWORD'),
    'verification_endpoint' => env('BKASH_VERIFICATION_ENDPOINT', 'https://www.bkashcluster.com:9081/dreamwave/merchant/trxcheck/sendmsg'),
    'merchant_number' => env('BKASH_MERCHANT_NUMBER', '01799444000'),
    'tokenized' => [
        'app_key' => env('BKASH_TOKENIZED_APP_KEY'),
        'app_secret' => env('BKASH_TOKENIZED_APP_SECRET'),
        'username' => env('BKASH_TOKENIZED_USERNAME'),
        'password' => env('BKASH_TOKENIZED_PASSWORD'),
        'url' => env('BKASH_TOKENIZED_URL')
    ],
    'payout' => [
        'app_key' => env('BKASH_PAYOUT_APP_KEY'),
        'app_secret' => env('BKASH_PAYOUT_APP_SECRET'),
        'username' => env('BKASH_PAYOUT_USERNAME'),
        'password' => env('BKASH_PAYOUT_PASSWORD'),
        'url' => env('BKASH_PAYOUT_URL')
    ]
];