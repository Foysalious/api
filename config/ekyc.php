<?php

return [
    'url' => env('SHEBA_EKYC_URL'),
    'client_id' => '1234',
    'client_secret' => 'abcd',
    'liveliness_base_url' => env('LIVELINESS_BASE_URL'),
    'liveliness_token' => env('LIVELINESS_TOKEN'),
    'liveliness_duration' => 1,
    'liveliness_rotation' => -90
];
