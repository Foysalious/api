<?php

return [
    'front_url' => env('SHEBA_FRONT_URL'),
    'admin_url' => env('SHEBA_ADMIN_URL'),
    'api_url' => env('SHEBA_API_URL'),
    'socket_url' => env('SHEBA_SOCKET_URL'),
    'socket_on' => env('SHEBA_SOCKET_ON', true),
    'partners_url' => env('SHEBA_PARTNERS_URL'),
    'db_backup' => env('SHEBA_DB_BACKUP', false),
//    'revision' => file_get_contents(base_path()."/revision"),
    'order_code_start' => 8000,
    'job_code_start' => 16000
];