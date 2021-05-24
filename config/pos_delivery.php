<?php


return [
    'mobile_banking_providers' => ['bkash', 'rocket'],
    'vendor_list' => [
        'own_delivery' => ['bn' => 'নিজস্ব পরিবহন ', 'en' => 'Own Delivery'], 'paperfly' => ['bn' => 'পেপারফ্লাই', 'en' => 'Paperfly']
    ],
    'api_url' => env('S_DELIVERY_API_URL'),

    'payment_method' => ['cheque','beftn','cash','bkash','rocket','nagad'],
    'account_type'  => ['bank','mobile']
];
