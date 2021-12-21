<?php

return [
    'payment_method' => ['cod', 'bkash', 'online', 'others', 'payment_link','qr_code','advance_balance','emi'],
    'warranty_unit' => [
        'day' => ['bn' => 'দিন', 'en' => 'day'], 'week' => ['bn' => 'সপ্তাহ', 'en' => 'week'], 'month' => ['bn' => 'মাস', 'en' => 'month'], 'year' => ['bn' => 'বছর', 'en' => 'year']
    ],
    'last_returned_order_for_v1' => env('LAST_RETURNED_POS_ORDER_FOR_V1', 123),
    'minimum_order_amount_for_emi' => 5000,
    'maximum_publishable_product_in_webstore_for_free_packages' => 10,
    'api_key' => env('SHEBA_POS_API_KEY'),
];
