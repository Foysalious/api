<?php
return [
    'account_details_url'   => env('SHEBA_PARTNER_END_URL') . '/' . 'neo-banking-account-details',
    'account_details_title' => 'প্রাইম ব্যাংক অ্যাকাউন্ট সম্পর্কিত তথ্য',
    'category_list'         => [
        'NEO_1' => [
            'nid_selfie'  => 'NIDSelfie',
            'institution' => 'Institution',
            'personal'    => 'Personal',
            'nominee'     => 'Nominee',
            'documents'   => 'Documents',
            'account'     => 'Account'
        ]
    ],
    'category_titles'       => [
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
        'nominee'     => [
            'en' => 'Nominee',
            'bn' => 'বনমিনি তথ্য '
        ],
        'documents'   => [
            'en' => 'Documents',
            'bn' => 'প্রয়ােজনীয় ডকুমেন্ট আপলোড '
        ],
        'account'     => [
            'en' => 'Account',
            'bn' => 'অ্যাকাউন্ট সম্পর্কিত তথ্য '
        ]

    ]
];
