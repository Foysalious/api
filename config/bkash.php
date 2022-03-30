<?php

return [
    '01799444000' => [
        'app_key' => env('BKASH_APP_KEY_017'),
        'app_secret' => env('BKASH_APP_SECRET_017'),
        'username' => env('BKASH_USERNAME_017'),
        'password' => env('BKASH_PASSWORD_017'),
        'url' => env('BKASH_URL_017')
    ],
    '01833922030'           => [
        'app_key'         => env('BKASH_APP_KEY_018'),
        'app_secret'      => env('BKASH_APP_SECRET_018'),
        'username'        => env('BKASH_USERNAME_018'),
        'password'        => env('BKASH_PASSWORD_018'),
        'url'             => env('BKASH_URL_018'),
        'merchant_number' => '01833922030'
    ],
    'old_username'          => env('BKASH_VERIFICATION_USERNAME'),
    'old_password'          => env('BKASH_VERIFICATION_PASSWORD'),
    'verification_endpoint' => env('BKASH_VERIFICATION_ENDPOINT', 'https://www.bkashcluster.com:9081/dreamwave/merchant/trxcheck/sendmsg'),
    'merchant_number' => env('BKASH_MERCHANT_NUMBER', '01799444000'),
    'tokenized' => [
        'app_key' => env('BKASH_TOKENIZED_APP_KEY', '7epj60ddf7id0chhcm3vkejtab'),
        'app_secret' => env('BKASH_TOKENIZED_APP_SECRET', '18mvi27h9l38dtdv110rq5g603blk0fhh5hg46gfb27cp2rbs66f'),
        'username' => env('BKASH_TOKENIZED_USERNAME', 'sandboxTokenizedUser01'),
        'password' => env('BKASH_TOKENIZED_PASSWORD', 'sandboxTokenizedUser12345'),
        'url' => env('BKASH_TOKENIZED_URL', 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized')
    ],
    'payout'                => [
        'app_key'    => env('BKASH_PAYOUT_APP_KEY'),
        'app_secret' => env('BKASH_PAYOUT_APP_SECRET'),
        'username'   => env('BKASH_PAYOUT_USERNAME'),
        'password'   => env('BKASH_PAYOUT_PASSWORD'),
        'url'        => env('BKASH_PAYOUT_URL')
    ],
    'client_url' => env('BKASH_CLIENT_URL', "https://bkash-client.dev-sheba.xyz"),
    'sManager'              => [
        'app_key'         => env('BKASH_APP_KEY_017'),
        'app_secret'      => env('BKASH_APP_SECRET_017'),
        'username'        => env('BKASH_USERNAME_017'),
        'password'        => env('BKASH_PASSWORD_017'),
        'url'             => env('BKASH_URL_017'),
        'merchant_number' => '01799444000'
    ],
    'marketplace'           => [
        'app_key'         => env('BKASH_APP_KEY_018'),
        'app_secret'      => env('BKASH_APP_SECRET_018'),
        'username'        => env('BKASH_USERNAME_018'),
        'password'        => env('BKASH_PASSWORD_018'),
        'url'             => env('BKASH_URL_018'),
        'merchant_number' => '01833922030'
    ],
    'default_url'           => env("BKASH_DEFAULT_URL", "https://checkout.sandbox.bka.sh/v1.2.0-beta"),
    'transaction_charge'=>env('BKASH_TRANSACTION_CHARGE',1.5)
];