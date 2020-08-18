<?php
return [
    'base_url'         => env('NAGAD_BASE_URL', 'http://sandbox.mynagad.com:10080'),
    'merchant_id'      => env('NAGAD_MERCHANT_ID', '683002007104225'),
    'private_key_path' => resource_path(env('NAGAD_MERCHANT_PRIVATE_KEY', 'assets/nagad/merchantPrivate.key')),
    'public_key_path'  => resource_path(env('NAGAD_PUBLIC_KEY_PATH', 'assets/nagad/pgPublic.key')),
    'context_path'     => 'remote-payment-gateway-1.0'
];
