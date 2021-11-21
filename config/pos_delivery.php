<?php

return [
    'mobile_banking_providers' => ['bkash', 'rocket'],
    'payment_method_for_bank' => ['beftn'],
    'vendor_list' => [
        'own_delivery' => ['bn' => 'নিজস্ব ডেলিভারি ', 'en' => 'Own Delivery'], 'paperfly' => ['bn' => 'পেপারফ্লাই', 'en' => 'Paperfly']
    ],
    'vendor_list_v2' => [
        'paperfly' => ['bn' => 'পেপারফ্লাই', 'en' => 'Paperfly' , 'icon' => config('sheba.s3_url').'pos/paperfly.png']
    ],
    'api_url' => env('S_DELIVERY_API_URL'),
    'server_ip' => env('S_DELIVERY_SERVER_IP'),

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
        'note' => 'ক্যাশ অন ডেলিভারির ক্ষেত্রে বিলের উপর ১%  চার্জ প্রযোজ্য।সিটির মধ্যে পরের দিন ডেলিভারি ও সপ্তাহে ৫ দিন মার্চেন্ট পেমেন্ট।'
    ],
    'cash_on_delivery_charge_percentage' => 1,
    'terms_and_condition_link' => 'https://www.paperfly.com.bd/faq.php'
];
