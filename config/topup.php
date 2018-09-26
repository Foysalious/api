<?php

return [
    'robi' =>  [
        'url' => env('ROBI_TOPUP_URL', 'http://202.134.12.103:9898/pretups/C2SReceiver'),
        'login_id' => env('ROBI_TOPUP_LOGIN', 'pretups'),
        'password' => env('ROBI_TOPUP_PASSWORD', 'pretups123'),
        'robi_pin' => env('ROBI_TOPUP_ROBI_PIN', '1971'),
        'airtel_pin' => env('ROBI_TOPUP_AIRTEL_PIN', '1972'),
        'robi_mid' => env('ROBI_TOPUP_ROBI_MID', '01849011359'),
        'airtel_mid' => env('ROBI_TOPUP_AIRTEL_MID', '01638779974')
    ]
];