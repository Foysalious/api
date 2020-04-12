<?php
return [
    'base_url'   => env('OK_WALLET_BASE_URL'),
    'account'    => env('OK_WALLET_ACCOUNT'),
    'api_key'    => env('OK_WALLET_API_KEY'),
    'api_secret' => env('OK_WALLET_API_SECRET'),
    'format'     => env('OK_WALLET_API_FORMAT', 'json'),
    'key_path'   => env('OK_WALLET_KEY_PATH', 'assets/ok-wallet/public.key')
];
