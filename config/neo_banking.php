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

    ],
    'category_form_items'   => [
        'personal' => [
            [
                'field_type' => 'header',
                'title'      => 'সাধারণ তথ্য'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'আবেদনকারীর নাম (বড় অক্ষরে)  *',
                'hint'          => 'ABUL KALAM AZAD',
                'name'          => 'applicant_name',
                'error_message' => 'আবেদনকারীর নাম পূরণ আবশ্যক',
                'is_editable'   => false
            ],
            [
                'field_type'    => 'date',
                'title'         => 'জন্ম তারিখ *',
                'name'          => 'birth_date',
                'hint'          => 'উদাহরণ: 01/01/2000',
                'error_message' => 'জন্ম তারিখ  পূরণ আবশ্যক"',
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'বাবার নাম  *',
                'name'          => 'father_name',
                'hint'          => 'ABUL KALAM',
                'error_message' => 'বাবার নাম পূরণ আবশ্যক',
                'is_editable'   => false
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'মায়ের নাম  *',
                'name'          => 'mother_name',
                'hint'          => 'Mrs. ABUL',
                'error_message' => 'মায়ের নাম পূরণ আবশ্যক',
                'is_editable'   => false,
            ],
            [
                'field_type' => 'editText',
                'title'      => 'স্বামী/ স্ত্রীর নাম (যদি থাকে)',
                'name'       => 'husband_or_wife_name',
                'hint'       => 'BUILLA AZAD',
                'mandatory'  => false,
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'েশা  *',
                'name'          => 'occupation_name',
                'hint'          => 'Farmer',
                'error_message' => 'েশার ধরণ পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'প্রতিষ্ঠান এর নাম লিখুন',
                'name'          => 'company_name',
                'hint'          => 'Sheba.xyz',
                'error_message' => 'রতিষ্ঠান এর নাম পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'ই-টিন নাম্বার  *',
                'name'          => 'etin_number',
                'hint'          => '4654453',
                'error_message' => 'ই-টিন নাম্বার পূরণ আবশ্যক'
            ],
            [
                'field_type'=>'editText',
                'title'=>'জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার  *',
                'name'=>'NID_passport_birth_cer_number',
                'hint'=>'654564544645464',
                'error_message'=>'জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার পূরণ আবশ্যক'
            ],
            [
                'field_type'=>'header',
                'title'=>'আপনার বর্তমান ঠিকানা'
            ],
            [
                'field_type'=>'MultipleView',
                'title'=>'',
                'name'=>'present_address',
                'views'=>[
                    ''
                ]
            ]
        ]
    ]
];

