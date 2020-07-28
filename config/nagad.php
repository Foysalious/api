<?php
return [
    'base_url'         => env('NAGAD_BASE_URL', 'http://sandbox.mynagad.com:10080'),
    'merchant_id'      => env('NAGAD_MERCHANT_ID', '683002007104225'),
    'private_key_path' => resource_path('assets/nagad/merchantPrivateKey.key'),
    'public_key_path'  => resource_path('assets/nagad/pgPublicKey.txt'),
    'context_path'     => 'remote-payment-gateway-1.0'
];
