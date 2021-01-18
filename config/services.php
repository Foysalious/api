<?php

return [
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'business_domain' => env('MAILGUN_DOMAIN_FOR_BUSINESS', 'sheba-business.com'),
        'secret' => env('MAILGUN_SECRET')
    ],
    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1'
    ],
    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET')
    ],
    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET')
    ],
    'facebook' => [
        'client_id' => '323841817978882',
        'client_secret' => '2e456b524617f8878dc4aeb7db93a128',
        'redirect' => env('FB_APP_REDIRECT')
    ]
];
