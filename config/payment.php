<?php

return [
    'cbl' =>  [
        'tunnel_host' => env('CBL_TUNNEL_HOST', '127.0.0.1'),
        'tunnel_port' => env('CBL_TUNNEL_PORT', '743'),
        'merchant_id' => env('CBL_MERCHANT_ID', '11122333'),
        'urls' => [
            'approve' => env('SHEBA_API_URL') . '/v2/payment/cbl/approved',
            'decline' => env('SHEBA_API_URL') . '/v2/payment/cbl/declined',
            'cancel' => env('SHEBA_API_URL') . '/v2/payment/cbl/cancelled',
        ]
    ]
];