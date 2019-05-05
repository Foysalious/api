<?php

return [
    'busbd' => [
        'is_active'     => env('BUSBD_IS_ACTIVE', 1),
        'base_url'      => env('BUSBD_BASE_URL', 'https://api1.bdtickets.tech'),
        'booking_port'  => env('BUSBD_BOOKING_PORT', '20102'),
        'api_version'   => env('BUSBD_API_VERSION', 'v2'),
        'login_email'   => env('BUSBD_LOGIN_EMAIL', "tp@sheba.xyz"),
        'login_pass'    => env('BUSBD_LOGIN_PASSWORD', 'NpCzs~Me-3eE7YU4EW99U23^-2e^5DAW')
    ],
    'pekhom' => [
        'is_active'     => env('PEKHOM_IS_ACTIVE', 1),
        'base_url'      => env('PEKHOM_BASE_URL', 'https://sandbox.connectpekhom.com'),
        'user_name'     => env('PEKHOM_USER_NAME', 'admin@zarss.com'),
        'api_key'       => env('PEKHOM_API_KEY', 'zarssxwebsitapps')
    ]
];