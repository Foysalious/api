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
    ]
];
