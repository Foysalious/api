<?php

return [
    'front_url' => env('SHEBA_FRONT_END_URL'),
    'admin_url' => env('SHEBA_BACKEND_URL'),
    'api_url' => env('SHEBA_API_URL'),
    's3_url' => env('S3_URL'),
    'socket_url' => env('SHEBA_SOCKET_URL'),
    'socket_on' => env('SHEBA_SOCKET_ON', true),
    'partners_url' => env('SHEBA_PARTNER_END_URL'),
    'db_backup' => env('SHEBA_DB_BACKUP', false),
    'portal' => 'manager-app',
    //'revision' => file_get_contents(base_path()."/revision"),
    'order_code_start' => 8000,
    'job_code_start' => 16000
];