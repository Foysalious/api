<?php

return [
    'driver' => 's3',
    'url' => env('S3_URL'),
    'key' => env('S3_KEY'),
    'secret' => env('S3_SECRET'),
    'region' => env('S3_REGION', 'ap-south-1'),
    'bucket' => env('S3_BUCKET'),
    'credentials' => ['key' => env('S3_KEY'), 'secret' => env('S3_SECRET')],
    'scheme' => 'http'
];
