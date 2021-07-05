<?php

return [
    'is_on' => env('SMS_SEND_ON', true),
    'service' => [
        'base_url' => env('SMS_SERVICE_BASE_URL', 'http://sms-service.dev-sheba.xyz'),
        'api_key' => env('SMS_SERVICE_API_KEY', 'secret')
    ]
];
