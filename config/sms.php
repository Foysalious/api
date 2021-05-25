<?php

/*
|----------------------------------------------------
| SMS Gateway configuration
|----------------------------------------------------
*/

return [
    'sslwireless' => [
        'sid' => env('SMS_SID'),
        'user' => env('SMS_USER'),
        'password' => env('SMS_PASSWORD')
    ],
    'infobip' => [
        'base_url' => env('INFOBIP_BASE_URL'),
        'from' => env('INFOBIP_SMS_FROM'),
        'user' => env('INFOBIP_SMS_USER_NAME'),
        'password' => env('INFOBIP_SMS_PASSWORD')
    ],
    "adareach" => [
        "url"       => env('ADAREACH_BASE_URL'),
        "username"  => env('ADAREACH_USERNAME'),
        "password"  => env('ADAREACH_PASSWORD'),
        "from"      => env('ADAREACH_FROM_NUMBER'),
    ],
    "boomcast" => [
        "url"       => env('BOOMCAST_BASE_URL'),
        "username"  => env('BOOMCAST_USERNAME'),
        "password"  => env('BOOMCAST_PASSWORD'),
        "mask"      => env('BOOMCAST_MASK'),
    ],
    "elitbuzz" => [
        "url"       => env('ELITBUZZ_BASE_URL'),
        "api_key"   => env('ELITBUZZ_API_KEY'),
        "sid"       => env('ELITBUZZ_SID'),
    ],
    'default-provider' => env('SMS_GATEWAY', 'sslwireless'),
    'is_on' => env('SMS_SEND_ON', true),
];
