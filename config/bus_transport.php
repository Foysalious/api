<?php

return [
    'bdticket' => [
        'is_active'     => env('BDTICKET_IS_ACTIVE', 1),
        'base_url'      => env('BDTICKET_BASE_URL', 'https://api1.bdtickets.tech'),
        'booking_port'  => env('BDTICKET_BOOKING_PORT', '20102'),
        'api_version'   => env('BDTICKET_API_VERSION', 'v2'),
        'login_email'   => env('BDTICKET_LOGIN_EMAIL', "tp@sheba.xyz"),
        'login_pass'    => env('BDTICKET_LOGIN_PASSWORD', 'NpCzs~Me-3eE7YU4EW99U23^-2e^5DAW'),
        'wallet_secret' => env('BDTICKET_WALLET_SECRET', 'bdtickets'),
        'authorization_port' => env('BDTICKET_AUTHORIZATION_PORT', '20100'),
        'balance_check_port' => env('BDTICKET_BALANCE_CHECK_PORT','20105')
    ],
    'pekhom' => [
        'is_active'     => env('PEKHOM_IS_ACTIVE', 1),
        'base_url'      => env('PEKHOM_BASE_URL', 'https://sandbox.connectpekhom.com'),
        'user_name'     => env('PEKHOM_USER_NAME', 'admin@zarss.com'),
        'api_key'       => env('PEKHOM_API_KEY', 'zarssxwebsitapps'),
        'wallet_secret' => env('PEKHOM_WALLET_SECRET', 'pekhom')
    ]
];