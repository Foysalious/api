<?php

return [
    'url' => env('SHEBA_EKYC_URL'),
    'client_id' => env('EKYC_CLIENT_ID', '1234'),
    'client_secret' => env('EKYC_CLIENT_SECRET', 'abcd'),
    'liveliness_base_url' => env('LIVELINESS_BASE_URL'),
    'liveliness_token' => env('LIVELINESS_TOKEN'),
    'liveliness_duration' => 1,
    'liveliness_rotation' => -90
];
