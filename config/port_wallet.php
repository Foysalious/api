<?php

return [
    'base_url' => env('PORT_WALLET_BASE_URL'),
    'app_key' => env('PORT_WALLET_APP_KEY'),
    'secret_key' => env('PORT_WALLET_SECRET_KEY'),
    'is_ipn_enabled' => env('PORT_WALLET_IPN_ENABLED', true),
    'ipn_url' =>  env('SHEBA_API_URL') . '/v2/payments/port-wallet/ipn',
    'validation_on_redirect_url' => env('SHEBA_API_URL') . '/v2/payments/port-wallet/validate-on-redirect',
    'redirect_without_validation_url' => env('SHEBA_API_URL') . '/v2/payments/port-wallet/redirect-without-validate',
];
