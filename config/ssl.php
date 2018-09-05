<?php

return [
    'store_id' => env('SSL_STORE_ID'),
    'store_password' => env('SSL_STORE_PASSWORD'),
    'success_url' => env('SHEBA_API_URL') . '/v2/paycharge/ssl/validate',
    'fail_url' => env('SHEBA_API_URL') . '/v2/paycharge/ssl/validate',
    'cancel_url' => env('SHEBA_API_URL') . '/v2/paycharge/ssl/validate',
    'session_url' => env('SSL_SESSION_URL'),
    'order_validation_url' => env('SSL_ORDER_VALIDATION_URL'),
];