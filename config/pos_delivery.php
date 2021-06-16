<?php


return [
    'mobile_banking_providers' => ['bkash', 'rocket'],
    'payment_method_for_bank' => ['beftn'],
    'vendor_list' => [
        'own_delivery' => ['bn' => 'নিজস্ব ডেলিভারি ', 'en' => 'Own Delivery'], 'paperfly' => ['bn' => 'পেপারফ্লাই', 'en' => 'Paperfly']
    ],
    'api_url' => env('S_DELIVERY_API_URL'),

    'payment_method' => ['beftn','bkash','rocket','nagad'],
    'account_type'  => ['bank','mobile'],
    'paperfly_charge' => [
        'inside_city' => [
            'minimum' => 50,
            'kg_wise' => 30
        ],
        'outside_city' => [
            'minimum' => 110,
            'kg_wise' => 30
        ],
        'note' => 'ক্যাশ অন ডেলিভারির ক্ষেত্রে বিলের উপর ১% চার্জ প্রযোজ্য।'
    ],
    'cash_on_delivery_charge_percentage' => 1
];
