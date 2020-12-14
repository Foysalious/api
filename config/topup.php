<?php

return [
    'robi' => [
        'url' => env('ROBI_TOPUP_URL', 'http://202.134.12.103:9898/pretups/C2SReceiver'),
        'login_id' => env('ROBI_TOPUP_LOGIN', 'pretups'),
        'password' => env('ROBI_TOPUP_PASSWORD', 'pretups123'),
        'gateway_code' => env('ROBI_TOPUP_GATEWAY_CODE', 'EXTGW'),
        'robi_mid' => env('ROBI_TOPUP_ROBI_MID', '01849011359'),
        'robi_pin' => env('ROBI_TOPUP_ROBI_PIN', '1972'),
        'airtel_mid' => env('ROBI_TOPUP_AIRTEL_MID', '01638779974'),
        'airtel_pin' => env('ROBI_TOPUP_AIRTEL_PIN', '1972')
    ],
    'bl' => [
        'url' => env('BL_TOPUP_URL'),
        'login_id' => env('BL_TOPUP_LOGIN'),
        'password' => env('BL_TOPUP_PASSWORD'),
        'gateway_code' => env('BL_TOPUP_GATEWAY_CODE'),
        'mid' => env('BL_TOPUP_MID'),
        'pin' => env('BL_TOPUP_PIN')
    ],
    'ssl' => [
        'proxy_url' => env('SSL_VR_PROXY_URL'),
        'client_id' => env('SSL_TOPUP_CLIENT_ID'),
        'client_password' => env('SSL_TOPUP_CLIENT_PASSWORD'),
        'url' => env('SSL_TOPUP_URL'),
    ],
    'status' => [
        'initiated' => ['sheba' => 'Initiated', 'partner' => 'Initiated', 'customer' => 'Initiated'],
        'pending' => ['sheba' => 'Pending', 'partner' => 'Active', 'customer' => 'Verified'],
        'successful' => ['sheba' => 'Successful', 'partner' => 'Inactive', 'customer' => 'Inactive'],
        'failed' => ['sheba' => 'Failed', 'partner' => 'Inactive', 'customer' => 'Blocked'],
    ],
    'paywell' => array(
        'username' => 'shebaxyz',
        'auth_password' => 'nH3vB0zP6gC2tI3',
        'password' => '73651646',
        'get_token_url' => 'https://agentapi.paywellonline.com/Authantication/PaywellAuth/getToken',
        'api_key' => 'f9d1b62eb863f39842e4ff56cb44510f648e3a987e9a8548b2ffda0331398609',
        'encryption_key' => '0b797747c3803efc2956c8f218c68a25f0e9b6dad5fa83dc95c53a76c160303167a5ceaa8356bfb5be9c765c83fcdb2ef54ed69614964576780caf3d41a36378',
        'single_topup_url' => 'https://agentapi.paywellonline.com/Recharge/mobileRecharge/singleTopup',
    ),
];
