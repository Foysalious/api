<?php
return [
    'stores' => [
        'default'   => [
            'base_url'         => env('NAGAD_BASE_URL', 'http://sandbox.mynagad.com:10080'),
            'merchant_id'      => env('NAGAD_MERCHANT_ID', '683002007104225'),
            'private_key_path' => resource_path(env('NAGAD_MERCHANT_PRIVATE_KEY', 'assets/nagad/merchantPrivate.key')),
            'public_key_path'  => resource_path(env('NAGAD_PUBLIC_KEY_PATH', 'assets/nagad/pgPublic.key')),
            'context_path'     => 'remote-payment-gateway-1.0',
        ],
        'affiliate' => [
            'base_url'         => env('NAGAD_BASE_URL', 'http://sandbox.mynagad.com:10080'),
            'merchant_id'      => env('NAGAD_MERCHANT_ID_BODNHU', '683002007104225'),
            'private_key_path' => resource_path(env('NAGAD_MERCHANT_PRIVATE_KEY_BONDHU', 'assets/nagad/Bondhu.merchantPrivate.key')),
            'public_key_path'  => resource_path(env('NAGAD_PUBLIC_KEY_PATH_BONDHU', 'assets/nagad/Bondhu.pgPublic.key')),
            'context_path'     => 'remote-payment-gateway-1.0',
        ]
    ]
];
