<?php

return [
    'store_id' => env('SSL_STORE_ID'),
    'store_password' => env('SSL_STORE_PASSWORD'),
//    'success_url' => env('SHEBA_API_URL') . '/v2/ssl/validate',
    'success_url' => env('SHEBA_API_URL') . '/v2/orders/payments/success',
//    'fail_url' => env('SHEBA_API_URL') . '/v2/ssl/validate',
    'fail_url' => env('SHEBA_API_URL') . '/v2/orders/payments/fail',
//    'cancel_url' => env('SHEBA_API_URL') . '/v2/ssl/validate',
    'cancel_url' => env('SHEBA_API_URL') . '/v2/orders/payments/cancel',
    'session_url' => env('SSL_SESSION_URL'),
    'order_validation_url' => env('SSL_ORDER_VALIDATION_URL'),
    'topup_client_id' => env('SSL_TOPUP_CLIENT_ID'),
    'topup_client_password' => env('SSL_TOPUP_CLIENT_PASSWORD'),
    'topup_url' => env('SSL_TOPUP_URL'),
];