<?php

/*
|----------------------------------------------------
| SMS Gateway configuration
|----------------------------------------------------
*/

return [
    'sslwireless' => [
        'sid'       => env('SMS_SID'),
        'user'      => env('SMS_USER'),
        'password'  => env('SMS_PASSWORD')
    ],
    'infobip' => [
        'base_url'  => env('INFOBIP_BASE_URL'),
        'from'      => env('INFOBIP_SMS_FROM'),
        'user'      => env('INFOBIP_SMS_USER_NAME'),
        'password'  => env('INFOBIP_SMS_PASSWORD')
    ],
    'default-provider' => 'sslwireless'
];