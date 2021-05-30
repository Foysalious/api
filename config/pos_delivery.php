<?php


return [
    'mobile_banking_providers' => ['bkash', 'rocket'],
    'payment_method_for_bank' => ['cheque','beftn','cash'],
    'vendor_list' => [
        'own_delivery' => ['bn' => 'নিজস্ব পরিবহন ', 'en' => 'Own Delivery'], 'paperfly' => ['bn' => 'পেপারফ্লাই', 'en' => 'Paperfly']
    ],
    'api_url' => env('S_DELIVERY_API_URL'),

    'payment_method' => ['cheque','beftn','cash','bkash','rocket','nagad'],
    'account_type'  => ['bank','mobile'],
    'paperfly_charge' => [
        'inside_city' => [
            'minimum' => 50,
            'kg_wise' => 80
        ],
        'outside_city' => [
            'minimum' => 50,
            'kg_wise' => 80
        ],
        'note' => 'ক্যাশ অন ডেলিভারি চার্জঃ ১% চার্জ প্রযোজ্য হবে বিলের উপর'

    ]
];
