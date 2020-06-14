<?php

return [
    'cbl' => [
        'tunnel_url' => env('CBL_TUNNEL_HOST'),
        'merchant_id' => env('CBL_MERCHANT_ID'),
        'urls' => [
            'approve' => env('SHEBA_API_URL') . '/v2/payments/cbl/success',
            'decline' => env('SHEBA_API_URL') . '/v2/payments/cbl/fail',
            'cancel' => env('SHEBA_API_URL') . '/v2/payments/cbl/cancel',
        ]
    ],
    'ssl' => [
        'stores' => [
            'default' => [
                'id' => env('SSL_STORE_ID'),
                'password' => env('SSL_STORE_PASSWORD'),
                'session_url' => env('SSL_SESSION_URL'),
                'order_validation_url' => env('SSL_ORDER_VALIDATION_URL'),
            ],
            'market_place' => [
                'id' => env('SSL_MARKET_PLACE_STORE_ID'),
                'password' => env('SSL_MARKET_PLACE_STORE_PASSWORD'),
                'session_url' => env('SSL_MARKET_PLACE_SESSION_URL'),
                'order_validation_url' => env('SSL_MARKET_PLACE_ORDER_VALIDATION_URL'),
            ],
            'donation' => [
                'id' => env('SSL_DONATION_STORE_ID'),
                'password' => env('SSL_DONATION_STORE_PASSWORD'),
                'session_url' => env('SSL_DONATION_SESSION_URL'),
                'order_validation_url' => env('SSL_DONATION_ORDER_VALIDATION_URL'),
            ]
        ],
        'urls' => [
            'success' => env('SHEBA_API_URL') . '/v2/orders/payments/success',
            'fail' => env('SHEBA_API_URL') . '/v2/orders/payments/fail',
            'cancel' => env('SHEBA_API_URL') . '/v2/orders/payments/cancel'
        ]
    ],
    'port_wallet' => [
        'base_url' => env('PORT_WALLET_BASE_URL'),
        'app_key' => env('PORT_WALLET_APP_KEY'),
        'secret_key' => env('PORT_WALLET_SECRET_KEY'),
        'is_ipn_enabled' => env('PORT_WALLET_IPN_ENABLED', true),
        'urls' => [
            'ipn' =>  env('SHEBA_API_URL') . '/v2/payments/port-wallet/ipn',
            'validation_on_redirect' => env('SHEBA_API_URL') . '/v2/payments/port-wallet/validate-on-redirect',
            'redirect_without_validation' => env('SHEBA_API_URL') . '/v2/payments/port-wallet/redirect-without-validate',
        ]
    ],
    'ok_wallet' => [
        'base_url'   => env('OK_WALLET_BASE_URL'),
        'account'    => env('OK_WALLET_ACCOUNT'),
        'api_key'    => env('OK_WALLET_API_KEY'),
        'api_secret' => env('OK_WALLET_API_SECRET'),
        'format'     => env('OK_WALLET_API_FORMAT', 'json'),
        'key_path'   => env('OK_WALLET_KEY_PATH', 'assets/ok-wallet/public.key'),
        'merchant'   => env('OK_WALLET_MERCHANT', 'sheba.xyz')
    ]
];
