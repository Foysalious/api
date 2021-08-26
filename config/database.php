<?php

return [
    'fetch' => PDO::FETCH_CLASS,
    'default' => env('DB_CONNECTION', 'mysql'),
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],
        'mysql' => [
            'driver' => 'mysql',
            'read' => [
                'host' => env('DB_READ_HOST', 'localhost'),
                'port' => env('DB_READ_PORT', 3306),
            ], 'write' => [
                'host' => env('DB_WRITE_HOST', 'localhost'),
                'port' => env('DB_WRITE_PORT', 3306)
            ],
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
            'sticky' => true
        ],
        'mysql2' => [
            'driver' => 'mysql',
            'read' => [
                'host' => env('DB_READ_HOST_SECOND', 'localhost'),
                'port' => env('DB_READ_PORT_SECOND', 3306),
            ], 'write' => [
                'host' => env('DB_WRITE_HOST_SECOND', 'localhost'),
                'port' => env('DB_WRITE_PORT_SECOND', 3306)
            ],
            'database' => env('DB_DATABASE_SECOND', 'forge'),
            'username' => env('DB_USERNAME_SECOND', 'forge'),
            'password' => env('DB_PASSWORD_SECOND', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ],
        'mongodb' => [
            'driver' => 'mongodb',
            'database' => env('MONGO_DB_DATABASE', 'sheba'),
            'dsn' => env('MONGO_DB_DSN')
        ]
    ],
    'migrations' => 'migrations',
    'redis' => [
        'cluster' => false,
        'default' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => 'redis',
        ],
        'clusters' => [
            'default' => [
                [
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'password' => env('REDIS_PASSWORD', null),
                    'port' => env('REDIS_PORT', 6379),
                    'database' => 0,
                ],
            ],
        ],
    ]
];
