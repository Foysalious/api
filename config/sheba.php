<?php

return [
    'front_url' => env('SHEBA_FRONT_END_URL'),
    'admin_url' => env('SHEBA_BACKEND_URL'),
    'api_url' => env('SHEBA_API_URL'),
    'logistic_url' => env('SHEBA_LOGISTIC_URL'),
    'business_url' => env('SHEBA_BUSINESS_URL'),
    'wallet_url' => env('SHEBA_WALLET_URL', 'https://wallet.sheba.xyz'),
    'payment_link_url' => env('SHEBA_PAYMENT_LINK_URL'),
    'payment_link_web_url' => env('SHEBA_PAYMENT_LINK_WEB_URL', 'https://payments.sheba.xyz'),
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
    'last_partner_order_id_v1' => env('LAST_PARTNER_ORDER_ID_V1'),
    'material_commission_rate' => 5.0,
    'portals' => ['admin-portal', 'partner-portal', 'manager-app', 'customer-app', 'customer-portal', 'resource-portal', 'resource-app', 'bondhu-app', 'bondhu-portal', 'automatic', 'business-portal'],
    'send_order_create_sms' => env('SEND_ORDER_CREATE_SMS', true),
    'stopped_sms_portal_for_customer' => ['customer-app', 'customer-portal', 'manager-app', 'partner-portal'],
    'push_notification_topic_name' => [
        'customer' => env('CUSTOMER_TOPIC_NAME', 'customer_'),
        'resource' => env('RESOURCE_TOPIC_NAME', 'resource_'),
        'manager' => env('MANAGER_TOPIC_NAME', 'manager_'),
        'employee' => env('EMPLOYEE_TOPIC_NAME', 'employee_'),
    ],
    'push_notification_channel_name' => [
        'customer' => 'customer_channel',
        'manager' => 'manager_channel',
        'resource' => 'resource_channel',
        'employee' => 'employee_channel'
    ],
    'push_notification_sound' => [
        'customer' => 'default',
        'manager' => 'notification_sound'
    ],
    'partner_packages' => [
        'ESP' => 4,
        'PSP' => 3,
        'LSP' => 2
    ],
    'rent_a_car_pickup_district_ids' => [1, 43],
    'partner_packages_on_partner_list' => [
        'ESP' => 2,
        'PSP' => 6,
        'LSP' => 2
    ],
    'partner_package_and_badge_order_on_partner_list' => [
        ['package' => 'ESP', 'badge' => 'gold'],
        ['package' => 'ESP', 'badge' => 'silver'],
        ['package' => 'PSP', 'badge' => 'silver'],
        ['package' => 'ESP', 'badge' => null],
        ['package' => 'PSP', 'badge' => null],
        ['package' => 'LSP', 'badge' => null],
    ],
    'weight_on_partner_list' => [
        'impression' => 0.4,
        'capacity' => 0.15,
        'orders' => 0.05,
        'avg_rating' => 0.3,
        'total_ratings' => 0.1
    ],
    'promo_applicable_sales_channels' => ['Web', 'App', 'App-iOS', 'E-Shop', 'Call-Center'],
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
    'gradients' => [
        ['#FF881B', '#FFCF1B'],
        ['#EA4D2C', '#FFA62E'],
        ['#F38181', '#FCE38A'],
        ['#F54EA2', '#FF7676'],
        ['#7117EA', '#EA6060'],
        ['#F030C1', '#6094EA'],
        ['#8441A4', '#FF5B94'],
        ['#3BB2B8', '#43E695'],
        ['#194F68', '#57CA85'],
        ['#5B247A', '#1BCEDF'],
        ['#00B8BA', '#00FFED']
    ],
    'screen' => ['home', 'eshop', 'payment_link', 'pos', 'inventory', 'referral', 'due'],
    'partner_lite_packages_id' => env('LITE_PACKAGE_ID'),
    'rent_a_car' => [
        'inside_city' => [
            'category' => [222]
        ],
        'outside_city' => [
            'category' => [223]
        ]
    ],
    'subscription_type' => [
        'customer' =>
            [
                'weekly' => [
                    'name' => 'weekly'
                ],
                'monthly' => [
                    'name' => 'monthly'
                ]
            ]
    ],
    'sheba_help_desk_id' => 1809,
    'best_deal_ids' => env('BEST_DEAL_IDS'),
    'service_group_ids' => env('SERVICE_GROUP_IDS'),
    'online_payment_discount_threshold_minutes' => null,
    'online_payment_discount_percentage' => env('ONLINE_PAYMENT_DISCOUNT_PERCENTAGE', 0),
    'car_rental' => [
        'master_category_id' => env('RENT_A_CAR_CATEGORY_ID'),
        'secondary_category_ids' => explode(',', env('RENT_CAR_IDS')),
        'one_way_id' => env('ONE_WAY_SERVICE_ID'),
        'round_trip_id' => env('ROUND_TRIP_SERVICE_ID'),
        'date_range_service_ids' => explode(',', env('RENT_A_CAR_SERVICE_ID_FOR_DATE_RANGE')),
        'destination_fields_service_ids' => array_map('intval', explode(',', env('RENT_A_CAR_SERVICE_ID_FOR_DESTINATION_FIELD'))),
        'service_ids' => array_map('intval', explode(',', env('RENT_CAR_SERVICE_IDS'))),
        'slug' => 'car-rental'
    ],
    'payment_link' => [
        'sms' => env('SEND_PAYMENT_LINK_SMS', 1)
    ],
    'min_order_amount_for_emi' => 5000,
    'gift_card_validity_month' => 6,
    'marketplace_not_accessible_packages_id' => explode(',', env('MARKETPLACE_NOT_ACCESSIBLE_PACKAGES_ID', '1,2')),
    'use_cdn_for_asset' => env('SHEBA_USE_CDN_FOR_ASSET', true),
    'category_groups' => [
        'trending' => env('TRENDING_CATEGORY_GROUP', 10)
    ],
    'payout_token'=>env('SHEBA_PAYOUT_TOKEN','ShebaAdminPanelToken!@#$!@#')
];
