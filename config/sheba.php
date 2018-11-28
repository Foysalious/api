<?php

return [
    'front_url' => env('SHEBA_FRONT_END_URL'),
    'admin_url' => env('SHEBA_BACKEND_URL'),
    'api_url' => env('SHEBA_API_URL'),
    's3_url' => env('S3_URL'),
    'socket_url' => env('SHEBA_SOCKET_URL'),
    'socket_on' => env('SHEBA_SOCKET_ON', true),
    'send_push_notifications' => env('SHEBA_SEND_PUSH_NOTIFICATIONS', true),
    'partners_url' => env('SHEBA_PARTNER_END_URL'),
    'db_backup' => env('SHEBA_DB_BACKUP', false),
    'portal' => 'customer-portal',
    //'revision' => file_get_contents(base_path()."/revision"),
    'order_code_start' => 8000,
    'job_code_start' => 16000,
    'portals' => [
        'admin-portal', 'partner-portal', 'manager-app', 'customer-app', 'customer-portal', 'resource-portal', 'resource-app', 'bondhu-app', 'automatic'
    ],
    'push_notification_topic_name' => [
        'customer' => env('CUSTOMER_TOPIC_NAME', 'customer_'),
        'resource' => env('RESOURCE_TOPIC_NAME', 'resource_'),
        'manager' => env('MANAGER_TOPIC_NAME', 'manager_')
    ],
    'push_notification_channel_name' => [
        'customer' => 'customer_channel',
        'manager' => 'manager_channel',
        'resource' => 'resource_channel'
    ],
    'push_notification_sound' => [
        'customer' => 'default',
        'manager' => 'notification_sound'
    ],
    'partner_packages' => [
        'ESP' => 3,
        'PSP' => 2,
        'LSP' => 1
    ],
    'rent_a_car_pickup_district_ids' => [1],
    'partner_packages_on_partner_list' => [
        'ESP' => 2,
        'PSP' => 6,
        'LSP' => 2
    ],
    'weight_on_partner_list' => [
        'impression' => 0.3,
        'capacity' => 0.2,
        'orders' => 0.15,
        'price' => 0.15,
        'avg_rating' => 0.12,
        'total_ratings' => 0.08
    ],
    'promo_applicable_sales_channels' => ['Web', 'App', 'App-iOS', 'E-Shop'],
    'category_colors' => [
        1 => '#78B9EB',
        3 => '#D5B4EB',
        221 => '#9BB9FB',
        17 => '#8B9EB',
        225 => '#7EE3FF',
        73 => '#5FE6D6',
        186 => '#73DA9E',
        224 => '#B2E59C',
        183 => '#F490C0',
        184 => '#CDEEAE',
        236 => '#FFE477',
        185 => '#FFC477',
        226 => '#FF9478',
        235 => '#FE7B7C',
        101 => '#C6C5CA',
    ],
    'gradiant1' => [
        '#FF881B',
        '#EA4D2C',
        '#F38181',
        '#F54EA2',
        '#7117EA',
        '#F030C1',
        '#8441A4',
        '#3BB2B8',
        '#194F68',
        '#5B247A',
        '#00B8BA'
    ],
    'gradiant2' => [
        '#FFCF1B',
        '#FFA62E',
        '#FCE38A',
        '#FF7676',
        '#EA6060',
        '#6094EA',
        '#FF5B94',
        '#43E695',
        '#57CA85',
        '#1BCEDF',
        '#00FFED'
    ]
];