<?php

return [
    "available_payment_gateway_keys" => ["ssl", "bkash"],
    'category_list' => [
        'ssl' => [
            'nid_selfie'  => 'NIDSelfie',
            'institution' => 'Institution',
            'personal'    => 'Personal',
            'documents'   => 'Documents'
        ],
        'bkash' => [
            'nid_selfie'  => 'NIDSelfie',
            'institution' => 'Institution',
            'personal'    => 'Personal',
            'documents'   => 'Documents'
        ],
    ],
    'category_titles'                    => [
        'nid_selfie'  => [
            'en' => 'NID and Selfie',
            'bn' => 'জাতীয় পরিচয়পত্র ও সেলফি'
        ],
        'institution' => [
            'en' => 'Institution',
            'bn' => 'প্রতিষ্ঠান সম্পর্কিত তথ্য'
        ],
        'personal'    => [
            'en' => 'Personal',
            'bn' => 'ব্যক্তিগত তথ্য '
        ],
        'documents'   => [
            'en' => 'Documents',
            'bn' => 'প্রয়ােজনীয় ডকুমেন্ট'
        ],
        'account'     => [
            'en' => 'Account',
            'bn' => 'অ্যাকাউন্ট সম্পর্কিত তথ্য '
        ]

    ],
    "category_form_items" => [
        'institution' => [
//            [
//                'id'          => 'header',
//                'input_type'  => 'header',
//                "title"       => "কোম্পানি প্রোফাইল",
//                'mandatory'   => false,
//                'is_editable' => false,
//            ],
            [
                'label'         => 'কোম্পানির নাম * ',
                'message'       => 'কোম্পানির নাম লিখুন',
                'id'            => 'company_name',
                'error'         => "কোম্পানির নাম পূরণ আবশ্যক",
                'input_type'    => 'text',
                'data'          => '',
                "min_length"    => "",
                "max_length"    => "",
                'is_editable'   => true,
                'mandatory'     => false,
                'data_source'   => 'partner',
                'data_source_id'=> 'name'
            ],
            [
                'label'         => 'ট্রেড লাইসেন্স নং *',
                'message'       => 'ট্রেড লাইসেন্স নং লিখুন',
                'id'            => 'trade_license',
                'error'         => "ট্রেড লাইসেন্স নং পূরণ আবশ্যক",
                'input_type'    => 'text',
                'data'          => '',
                "min_length"    => "",
                "max_length"    => "",
                'is_editable'   => true,
                'mandatory'     => false,
                'data_source'   => 'partner_basic_information',
                'data_source_id'=> 'trade_license'
            ],
            [
                'label'         => 'কোম্পানির টাইপ *',
                'message'       => '',
                'id'            => 'company_type',
                'error'         => "কোম্পানির টাইপ পূরণ আবশ্যক",
                'input_type'    => 'dropdown',
                'data'          => '',
                "min_length"    => "",
                "max_length"    => "",
                'is_editable'   => true,
                'mandatory'     => false,
                'data_source'   => 'partner',
                'data_source_id'=> 'business_type'
            ],
        ],
    ]
];