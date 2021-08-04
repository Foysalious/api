<?php

$top_up_queues = (require __DIR__ . DIRECTORY_SEPARATOR . 'topup_queues.php')['connections'];

return [
    /*
    |--------------------------------------------------------------------------
    | Default Queue Driver
    |--------------------------------------------------------------------------
    |
    | The Laravel queue API supports a variety of back-ends via an unified
    | API, giving you convenient access to each back-end using the same
    | syntax for each one. Here you may set the default queue driver.
    |
    | Supported: "null", "sync", "database", "beanstalkd", "sqs", "redis"
    |
    */
    'default' => env('QUEUE_DRIVER', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    */
    'connections' => [
        'sync' => [
            'driver' => 'sync'
        ],
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'expire' => 60
        ],
        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'queue' => 'default',
            'ttr' => 60
        ],
        'sqs' => [
            'driver' => 'sqs',
            'key' => 'your-public-key',
            'secret' => 'your-secret-key',
            'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
            'queue' => 'your-queue-name',
            'region' => 'us-east-1'
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'api_queue',
            'expire' => 60
        ],
        'test' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'test',
            'expire' => 60
        ],
        'sms_campaign' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'sms_campaign',
            'expire' => 60
        ],
        'report' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'report',
            'expire' => 60
        ],
        'business_notification' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'business_notification',
            'expire' => 60
        ],
        'pos_rebuild_data_migration' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'pos_rebuild_data_migration',
            'expire' => 60
        ],
        ] + $top_up_queues,

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */
    'failed' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'queue_failed_jobs',
    ]
];
