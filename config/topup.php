<?php

return [
    'robi' => [
        'proxy_url' => env('ROBI_PROXY_URL', 'http://proxy.dev-sheba.xyz/robi.php'),
        'url' => env('ROBI_TOPUP_URL', 'http://202.134.12.103:9898/pretups/C2SReceiver'),
        'login_id' => env('ROBI_TOPUP_LOGIN', 'pretups'),
        'password' => env('ROBI_TOPUP_PASSWORD', 'pretups123'),
        'gateway_code' => env('ROBI_TOPUP_GATEWAY_CODE', 'EXTGW'),
        'robi_pin' => env('ROBI_TOPUP_ROBI_PIN', '1972'),
        'airtel_pin' => env('ROBI_TOPUP_AIRTEL_PIN', '1972'),
        'robi_mid' => env('ROBI_TOPUP_ROBI_MID', '01849011359'),
        'airtel_mid' => env('ROBI_TOPUP_AIRTEL_MID', '01638779974')
    ],
    'status' => [
        'pending' => ['sheba' => 'Pending', 'partner' => 'Active', 'customer' => 'Verified'],
        'successful' => ['sheba' => 'Successful', 'partner' => 'Inactive', 'customer' => 'Inactive'],
        'failed' => ['sheba' => 'Failed', 'partner' => 'Inactive', 'customer' => 'Blocked'],
    ],
];