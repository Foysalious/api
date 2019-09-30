<?php

/*
|--------------------------------------------------------------------------
| S3 configuration
|--------------------------------------------------------------------------
|
| Though these configurations are already in filesystems.php,
| its registered here for using it simply by calling s3.*
| directly instead of the long filesystems.disks.s3.*
*/

return [
    'driver' => 's3',
    'url' => env('S3_URL'),
    'key' => env('S3_KEY'),
    'secret' => env('S3_SECRET'),
    'region' => env('S3_REGION'),
    'bucket' => env('S3_BUCKET'),
    'credentials' => ['key' => env('S3_KEY'), 'secret' => env('S3_SECRET')],
    'scheme' => 'http'
];