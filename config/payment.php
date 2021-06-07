<?php

return [
    'cbl'         => [
        'tunnel_url'  => env('CBL_TUNNEL_HOST'),
        'merchant_id' => env('CBL_MERCHANT_ID'),
        'urls'        => [
            'approve' => env('SHEBA_API_URL') . '/v2/payments/cbl/success',
            'decline' => env('SHEBA_API_URL') . '/v2/payments/cbl/fail',
            'cancel'  => env('SHEBA_API_URL') . '/v2/payments/cbl/cancel',
        ]
    ],
    'ssl'         => [
        'stores' => [
            'default'      => [
                'id'                   => env('SSL_STORE_ID'),
                'password'             => env('SSL_STORE_PASSWORD'),
                'session_url'          => env('SSL_SESSION_URL'),
                'order_validation_url' => env('SSL_ORDER_VALIDATION_URL'),
            ],
            'market_place' => [
                'id'                   => env('SSL_MARKET_PLACE_STORE_ID'),
                'password'             => env('SSL_MARKET_PLACE_STORE_PASSWORD'),
                'session_url'          => env('SSL_MARKET_PLACE_SESSION_URL'),
                'order_validation_url' => env('SSL_MARKET_PLACE_ORDER_VALIDATION_URL'),
            ],
            'donation'     => [
                'id'                   => env('SSL_DONATION_STORE_ID'),
                'password'             => env('SSL_DONATION_STORE_PASSWORD'),
                'session_url'          => env('SSL_DONATION_SESSION_URL'),
                'order_validation_url' => env('SSL_DONATION_ORDER_VALIDATION_URL'),
            ]
        ],
        'urls'   => [
            'refund'  => env('SSL_REFUND_URL'),
            'success' => env('SHEBA_API_URL') . '/v2/orders/payments/success',
            'fail'    => env('SHEBA_API_URL') . '/v2/orders/payments/fail',
            'cancel'  => env('SHEBA_API_URL') . '/v2/orders/payments/cancel'
        ]
    ],
    'port_wallet' => [
        'base_url'       => env('PORT_WALLET_BASE_URL'),
        'app_key'        => env('PORT_WALLET_APP_KEY'),
        'secret_key'     => env('PORT_WALLET_SECRET_KEY'),
        'is_ipn_enabled' => env('PORT_WALLET_IPN_ENABLED', true),
        'urls'           => [
            'ipn'                         => env('SHEBA_API_URL') . '/v2/payments/port-wallet/ipn',
            'validation_on_redirect'      => env('SHEBA_API_URL') . '/v2/payments/port-wallet/validate-on-redirect',
            'redirect_without_validation' => env('SHEBA_API_URL') . '/v2/payments/port-wallet/redirect-without-validate',
        ]
    ],
    'ok_wallet'   => [
        'base_url'   => env('OK_WALLET_BASE_URL'),
        'account'    => env('OK_WALLET_ACCOUNT'),
        'api_key'    => env('OK_WALLET_API_KEY'),
        'api_secret' => env('OK_WALLET_API_SECRET'),
        'format'     => env('OK_WALLET_API_FORMAT', 'json'),
        'key_path'   => env('OK_WALLET_KEY_PATH', 'assets/ok-wallet/public.key'),
        'merchant'   => env('OK_WALLET_MERCHANT', 'sheba.xyz')
    ],
    'nagad'       => [
        'stores' => [
            'default'     => [
                'base_url'         => env('NAGAD_BASE_URL', 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0'),
                'merchant_id'      => env('NAGAD_MERCHANT_ID', '683002007104225'),
                'private_key_path' => resource_path(env('NAGAD_MERCHANT_PRIVATE_KEY', 'assets/nagad/merchantPrivate.key')),
                'public_key_path'  => resource_path(env('NAGAD_PUBLIC_KEY_PATH', 'assets/nagad/pgPublic.key')),
                'context_path'     => 'remote-payment-gateway-1.0'
            ],
            'affiliate'   => [
                'base_url'         => env('NAGAD_BASE_URL', 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0'),
                'merchant_id'      => env('NAGAD_AFFILIATE_MERCHANT_ID', '683002007104225'),
                'private_key_path' => resource_path(env('NAGAD_AFFILIATE_MERCHANT_PRIVATE_KEY', 'assets/nagad/Bondhu.merchantPrivate.key')),
                'public_key_path'  => resource_path(env('NAGAD_AFFILIATE_PUBLIC_KEY_PATH', 'assets/nagad/Bondhu.pgPublic.key')),
                'context_path'     => 'remote-payment-gateway-1.0'
            ],
            'marketplace' => [
                'base_url'         => env('NAGAD_BASE_URL', 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0'),
                'merchant_id'      => env('NAGAD_MARKETPLACE_MERCHANT_ID', '686200110675045'),
                'private_key_path' => resource_path(env('NAGAD_MARKETPLACE_MERCHANT_PRIVATE_KEY', 'assets/nagad/Marketplace.merchantPrivate.key')),
                'public_key_path'  => resource_path(env('NAGAD_MARKETPLACE_PUBLIC_KEY_PATH', 'assets/nagad/Marketplace.pgPublic.key')),
                'context_path'     => 'remote-payment-gateway-1.0'
            ],
        ]
    ],
    'ebl'         => [
        'stores' => [
            'default' => [
                'base_url'     => env('EBL_BASE_URL', 'https://testsecureacceptance.cybersource.com'),
                'access_key'   => env('EBL_ACCESS_KEY', '59f2e88276b23a609a41bbc855eaeaad'),
                'profile_id'   => env('EBL_PROFILE_ID', '189C3320-583D-4148-984B-8193431BD3BB'),
                'profile_name' => env('EBL_PROFILE_NAME', 'SHEBA XYZ'),
                'signature'    => env('EBL_SIGNATURE', 'HMAC-SHA256'),
                'merchant_id'  => env('EBL_MERCHANT_ID', '20010024'),
                'account_id'   => env('EBL_ACCOUNT_ID', '20010024_acct'),
                'secret_key'   => env('EBL_SECRET_KEY_PATH', resource_path('assets/ebl/keySecret.txt'))
            ]
        ]
    ]
];
