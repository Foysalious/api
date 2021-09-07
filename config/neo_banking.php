<?php

if (!function_exists('addressViews')) {
    function addressViews($type, $defaultCountry='Bangladesh')
    {
        return [
            [
                'field_type'    => 'editText',
                'title'         => 'স্ট্রিট নং / গ্রামের নাম *',
                'name'          => 'street_village_' . $type . '_address',
                'id'            => 'street_village_' . $type . '_address',
                'hint'          => 'স্ট্রিট নং / গ্রামের নাম',
                'error_message' => 'স্ট্রিট নং / গ্রামের নাম  পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'পোস্ট কোড *',
                'name'          => 'postcode_' . $type . '_address',
                'id'            => 'postcode_' . $type . '_address',
                'hint'          => 'পোস্ট কোড',
                'error_message' => 'পোস্ট কোড  পূরণ আবশ্যক',
                'input_type'    => 'number',
            ],
            [
                'field_type'    => 'dropdown',
                'title'         => 'জেলা *',
                'name'          => 'district_' . $type . '_address',
                'id'            => 'district_' . $type . '_address',
                'hint'          => 'জেলা',
                'list_type'     => 'new_page_radio',
                'error_message' => 'জেলার নাম পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'dropdown',
                'title'         => 'থানা / উপজেলা *',
                'list_type'     => 'new_page_radio',
                'name'          => 'sub_district_' . $type . '_address',
                'id'            => 'sub_district_' . $type . '_address',
                'hint'          => 'থানা / উপজেলা',
                'error_message' => 'থানা / উপজেলা নাম পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'দেশ *',
                'name'          => 'country_' . $type . '_address',
                'id'            => 'country_' . $type . '_address',
                'hint'          => 'দেশ',
                'error_message' => 'দেশের নাম পূরণ আবশ্যক',
                'value'         => $defaultCountry,
            ]
        ];
    }
}

if (!function_exists('booleanView')) {
    function booleanView($type)
    {
        return [
            [
                'field_type' => 'radioButton',
                'name'       => $type . '_yes',
                'id'         => $type . '_yes',
                'title'      => 'Yes',
                'mandatory'  => false,
                'value'      => "1"
            ],
            [
                'field_type' => 'radioButton',
                'name'       => $type . '_no',
                'id'         => $type . '_no',
                'title'      => 'No',
                'mandatory'  => false,
                'value'      => "0"
            ]
        ];
    }
}

return [
    'prime_bank_sbs_url'                 => env('PRIME_BANK_NEO_BANKING_URL'),
    'account_details_url'                => env('SHEBA_PARTNER_END_URL') . '/' . 'api/bank-info',
    'account_details_title'              => 'প্রাইম ব্যাংক অ্যাকাউন্ট সম্পর্কিত তথ্য',
    'sbs_access_token'                   => env('SBS_ACCESS_TOKEN', '1234567890'),
    'sbs_client_id'                      => env('PRIME_BANK_NEO_BANKING_CLIENT_ID', '123456'),
    'sbs_client_secret'                  => env('PRIME_BANK_NEO_BANKING_CLIENT_SECRET', 'abcd'),
    'completion_success_message'         => "প্রয়োজনীয় তথ্য দেয়া সম্পন্ন হয়েছ, আপনি ব্যাংক অ্যাকাউন্ট জন্য আবেদন করতে পারবেন।",
    'completion_info_message'            => "ব্যাংক অ্যাকাউন্ট জন্য আবেদন করতে হলে নিচের তথ্যাবলি অবশ্যই প্রদান করতে হবে।",
    'category_list'                      => [
        'NEO_1' => [
            'nid_selfie'  => 'NIDSelfie',
            'institution' => 'Institution',
            'personal'    => 'Personal',
            'nominee'     => 'Nominee',
            'documents'   => 'Documents'
        ]
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
        'nominee'     => [
            'en' => 'Nominee',
            'bn' => 'নমিনি তথ্য '
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
    'category_form_items'                => [
        'dynamic_banner' => [
            [
                "field_type" => "banner",
                "title"      => "সকল সাধারন তথ্য দিতে NID দিয়ে আসা লাগবে। ",
                'mandatory'  => false,
                "purpose"    => "NID Submit"
            ]
        ],
        'personal'    => [
            [
                'field_type' => 'header',
                'title'      => 'সাধারণ তথ্য',
                'mandatory'  => false,
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'আবেদনকারীর নাম (বড় অক্ষরে)  *',
                'hint'          => 'ABUL KALAM AZAD',
                'name'          => 'applicant_name',
                'id'            => 'applicant_name',
                'error_message' => 'আবেদনকারীর নাম পূরণ আবশ্যক',
                'is_editable'   => false
            ],
            [
                'field_type'    => 'date',
                'title'         => 'জন্ম তারিখ *',
                'name'          => 'birth_date',
                'id'            => 'birth_date',
                'hint'          => 'উদাহরণ: 01/01/2000',
                'error_message' => 'জন্ম তারিখ পূরণ আবশ্যক',
                'is_editable'   => false
            ],
            [
                'field_type' => 'header',
                'title'      => 'লিঙ্গ',
                'mandatory'  => false
            ],
            [
                'field_type' => 'radioGroup',
                'title'      => '',
                'name'       => 'gender',
                'id'         => 'gender',
                'mandatory'  => true,
                'error_message' => 'লিঙ্গ পূরণ আবশ্যক',
                'views'      => [
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'male',
                        'id'         => 'male',
                        'title'      => 'পুরুষ',
                        'mandatory'  => false,
                        'value'      => 1
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'female',
                        'id'         => 'female',
                        'title'      => 'নারী',
                        'mandatory'  => false,
                        'value'      => 0
                    ]
                ]
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'বাবার নাম  *',
                'name'          => 'father_name',
                'id'            => 'father_name',
                'hint'          => 'উদাহরণ: Abdul Kader',
                'error_message' => 'বাবার নাম পূরণ আবশ্যক',
                'is_editable'   => false
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'বাবার নাম (English) *',
                'name'          => 'father_name_en',
                'id'            => 'father_name_en',
                'hint'          => 'উদাহরণ: Abdul Kader',
                'error_message' => 'বাবার নাম পূরণ আবশ্যক',
                'is_editable'   => true
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'মায়ের নাম  *',
                'name'          => 'mother_name',
                'id'            => 'mother_name',
                'hint'          => 'উদাহরণ: Salma Begum',
                'error_message' => 'মায়ের নাম পূরণ আবশ্যক',
                'is_editable'   => false
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'মায়ের নাম  (English) *',
                'name'          => 'mother_name_en',
                'id'            => 'mother_name_en',
                'hint'          => 'উদাহরণ: Salma Begum',
                'error_message' => 'মায়ের নাম পূরণ আবশ্যক',
                'is_editable'   => true
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'স্বামী/ স্ত্রীর নাম (যদি থাকে)',
                'name'          => 'husband_or_wife_name',
                'id'            => 'husband_or_wife_name',
                'hint'          => 'উদাহরণ: Salma Begum',
                'mandatory'     => false,
                'error_message' => 'স্বামী/ স্ত্রীর নাম পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'পেশা *',
                'name'          => 'occupation_name',
                'id'            => 'occupation_name',
                'hint'          => 'উদাহরণ: ব্যবসা',
                'value'         => 'Business',
                'is_editable'   => false,
                'error_message' => 'পেশার ধরণ পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'প্রতিষ্ঠান এর নাম লিখুন *',
                'name'          => 'company_name',
                'id'            => 'company_name',
                'hint'          => 'Your Company Name',
                'error_message' => 'প্রতিষ্ঠান এর নাম পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'ই-টিন নাম্বার  *',
                'name'          => 'etin_number',
                'id'            => 'etin_number',
                'hint'          => '4654453',
                'error_message' => 'ই-টিন নাম্বার পূরণ আবশ্যক',
                'input_type'    => 'number'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার  *',
                'name'          => 'nid_passport_birth_cer_number',
                'id'            => 'nid_passport_birth_cer_number',
                'hint'          => '654564544645464',
                'error_message' => 'জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার পূরণ আবশ্যক',
                'is_editable'   => false
            ],
            [
                'field_type' => 'header',
                'title'      => 'আপনার বর্তমান ঠিকানা',
                'mandatory'  => false
            ],
            [
                'field_type' => 'multipleView',
                'title'      => '',
                'name'       => 'present_address',
                'id'         => 'present_address',
                'views'      => addressViews('present'),
                'mandatory'  => true
            ],
            [


                'field_type'    => 'checkbox',
                'name'          => 'present_permanent_same_address_checked',
                'id'            => 'present_permanent_same_address_checked',
                "error_message" => "",
                "title"         => 'বর্তমান ঠিকানা এবং স্থায়ী ঠিকানা একই',
                'value'         => 0,
                'mandatory'     => false
            ],
            [
                'field_type' => 'header',
                'title'      => 'আপনার স্থায়ী ঠিকানা',
                'mandatory'  => false
            ],
            [
                'field_type' => 'multipleView',
                'title'      => '',
                'name'       => 'permanent_address',
                'id'         => 'permanent_address',
                'mandatory'  => true,
                'views'      => addressViews('permanent')
            ],
            [
                'field_type' => 'header',
                'title'      => 'ব্রাঞ্চ তথ্য',
                'mandatory'  => false
            ],
            [
                'field_type'    => 'dropdown',
                'title'         => 'ব্রাঞ্চ কোড *',
                'name'          => 'branch_code',
                'id'            => 'branch_code',
                'hint'          => 'এইখানে সিলেক্ট করুন',
                'list_type'     => 'dialog',
                'error_message' => 'ব্রাঞ্চ কোড পূরণ আবশ্যক'
            ],
            [
                'field_type' => 'header',
                'title'      => 'PEP/ IP তথ্য',
                'mandatory'  => false
            ],
            [
                'field_type' => 'radioGroup',
                'title'      => 'আপনি কি একজন PEP/ IP / বৈদেশিক সংস্থার নির্বাহী /ঊচ্চ  পদস্থ কর্মকর্তা? *',
                'name'       => 'pep_ip_status',
                'id'         => 'pep_ip_status',
                'value'      => '',
                'mandatory'  => true,
                'error_message' => 'PEP/ IP কর্মকর্তা কিনা, পূরণ আবশ্যক',
                'views'      => [
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'pep_ip_status_yes',
                        'id'         => 'pep_ip_status_yes',
                        'title'      => 'হ্যাঁ',
                        'mandatory'  => false,
                        'value'      => 0
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'pep_ip_status_no',
                        'id'         => 'pep_ip_status_no',
                        'title'      => 'না',
                        'mandatory'  => false,
                        'value'      => 0
                    ]
                ]
            ],
            [
                'field_type' => 'radioGroup',
                'title'      => 'আপনি কি একজন PEP/ IP  / বৈদেশিক সংস্থার নির্বাহী / ঊচ্চ পদস্থ কর্মকর্তার সাথে সংশ্লিষ্ট সহযোগী অথবা পারিবারিক সদস্য? *',
                'name'       => 'pep_ip_relation',
                'id'         => 'pep_ip_relation',
                'value'      => '',
                'mandatory'  => true,
                'error_message' => 'PEP/ IP কর্মকর্তার সাথে সংশ্লিষ্ট সহযোগী কিনা, পূরণ আবশ্যক',
                'views'      => [
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'pep_ip_relation_yes',
                        'id'         => 'pep_ip_relation_yes',
                        'title'      => 'হ্যাঁ',
                        'mandatory'  => false,
                        'value'      => 0
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'pep_ip_relation_no',
                        'id'         => 'pep_ip_relation_no',
                        'title'      => 'না',
                        'mandatory'  => false,
                        'value'      => 0
                    ]
                ]
            ],
            [
                'field_type'    => 'checkbox',
                'name'          => 'pep_ip_definition_read',
                'id'            => 'pep_ip_definition_read',
                "error_message" => "",
                "title"         => "পেপ / আই পি এর <u>সংজ্ঞা</u> আমি পড়েছি এবং বুঝেছি",
                'value'         => 0,
                'mandatory'     => false,
                'purpose'       => env('SHEBA_PARTNER_END_URL') . '/' . env('SHEBA_PARTNERS_URL_PREFIX')."/pbl/pep-ip-definition"
            ],
            [
                'field_type' => 'header',
                'title'      => 'FATCA তথ্য',
                'mandatory'  => false
            ],
            [
                'field_type' => 'radioGroup',
                'title'      => 'আপনি কি যুক্তরাষ্ট্রের সাথে সম্পৃক্ত (বসবাসকারী, নাগরিক, গ্রীন কার্ডধারী, যুক্তরাষ্ট্র / যুক্তরাষ্ট্রের মালিকানাধীন প্রতিষ্ঠান) *',
                'name'       => 'fatca_information',
                'id'         => 'fatca_information',
                'value'      => '',
                'mandatory'  => true,
                'error_message' => 'যুক্তরাষ্ট্রের সাথে সম্পৃক্ত কিনা, পূরণ আবশ্যক',
                'views'      => [
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'fatca_information_yes',
                        'id'         => 'fatca_information_yes',
                        'title'      => 'হ্যাঁ',
                        'mandatory'  => false,
                        'value'      => 0
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'fatca_information_no',
                        'id'         => 'fatca_information_no',
                        'title'      => 'না',
                        'mandatory'  => false,
                        'value'      => 1
                    ]
                ]
            ],
            [
                "field_type" => "warning",
                "title"      => "হ্যাঁ সিলেক্ট করলে, প্রাইম ব্যাংকের ব্রাঞ্চে গিয়ে, FATCA সম্পৃক্ত ডকুমেন্ট সহ যোগাযোগ করতে হবে।",
                'mandatory'  => false
            ],

        ],
        'institution' => [
            [
                'field_type'  => 'header',
                "title"       => "যোগাযোগ এর তথ্য",
                'mandatory'   => false,
                'is_editable' => false,
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'মোবাইল নাম্বার  *',
                'hint'          => '017*******',
                'name'          => 'mobile',
                'id'            => 'mobile',
                'error_message' => "মোবাইল নাম্বার পূরণ আবশ্যক",
                'input_type'    => 'phone',
                'is_editable'   => false
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'ই-মেইল আইডি *',
                'name'          => 'email',
                'id'            => 'email',
                'hint'          => 'arafat@gmail.com',
                'error_message' => 'ই-মেইল আইডি পূরণ আবশ্যক',
                'input_type'    => 'email',
                'mandatory'     => true,
                'purpose'       => 'আপনার প্রদত্ত এই ই-মেইল, প্রাইম ব্যাংক কর্তৃক ই-স্টেটমেন্ট ও ইন্টারনেট ব্যাংকিং সেবা প্রদানের উদ্দেশ্যে ব্যবহার করা হবে।',
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'আপনার প্রতিষ্ঠানের নাম (বড় অক্ষরে) *',
                'name'          => 'company_name',
                'id'            => 'company_name',
                'hint'          => 'AZAD TELECOM',
                'error_message' => 'প্রতিষ্ঠানের নাম  পূরণ আবশ্যক',
            ],
            [
                'field_type'  => 'header',
                "title"       => "ট্রেড লাইসেন্স সম্পর্কিত তথ্য",
                'mandatory'   => false,
                'is_editable' => false,
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'ট্রেড লাইসেন্স নং *',
                'name'          => 'trade_licence_number',
                'id'            => 'trade_licence_number',
                'hint'          => 'এখানে লিখুন',
                'error_message' => 'ট্রেড লাইসেন্স নং পূরণ আবশ্যক',
                'input_type'    => 'number',

            ],
            [
                'field_type'    => 'date',
                'title'         => 'ট্রেড লাইসেন্স মেয়াদ উত্তির্নের তারিখ *',
                'name'          => 'trade_license_expire_date',
                'id'            => 'trade_license_expire_date',
                'hint'          => 'উদাহরণ: 01/01/2000',
                'error_message' => 'ট্রেড লাইসেন্স মেয়াদ উত্তির্নের তারিখ পূরণ আবশ্যক',
                'input_type'    => 'number',
                'mandatory'     => true,
                'future_date'   => true
            ],
            [
                'field_type'    => 'date',
                'title'         => 'নিবন্ধনের তারিখ *',
                'name'          => 'trade_licence_date',
                'id'            => 'trade_licence_date',
                'hint'          => 'উদাহরণ: 01/01/2000',
                'error_message' => 'নিবন্ধনের তারিখ  পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => "ট্রেড লাইসেন্স প্রদান কারি কর্তৃপক্ষ *",
                'name'          => 'issue_authority',
                'id'            => 'issue_authority',
                'hint'          => '',
                'error_message' => 'ট্রেড লাইসেন্স প্রদান কারি কর্তৃপক্ষ পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => "অনুমোদনকারী প্রতিষ্ঠান *",
                'name'          => 'grantor_organization',
                'id'            => 'grantor_organization',
                'hint'          => '',
                'error_message' => 'অনুমোদনকারী প্রতিষ্ঠানের নাম পূরণ আবশ্যক '
            ],
            [
                'field_type'  => 'header',
                "title"       => "রেজিস্ট্রেশন সম্পর্কিত তথ্য",
                'mandatory'   => false,
                'is_editable' => false,
            ],
            [
                'field_type'    => 'editText',
                'title'         => "রেজিস্ট্রেশন নং",
                'name'          => 'registration_number',
                'id'            => 'registration_number',
                'hint'          => '90145',
                'error_message' => "রেজিস্ট্রেশন নং পূরণ আবশ্যক",
                'mandatory'     => false,
                'input_type'    => 'number'
            ],
            [
                'field_type'    => 'date',
                'title'         => 'নিবন্ধনের তারিখ ',
                'name'          => 'registration_date',
                'id'            => 'registration_date',
                'hint'          => 'উদাহরণ: 01/01/2000',
                'error_message' => 'নিবন্ধনের তারিখ  পূরণ আবশ্যক"',
                'mandatory'     => false,
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'অনুমোদনকারী প্রতিষ্ঠান এবং দেশ',
                'name'          => 'grantor_organization_and_country',
                'id'            => 'grantor_organization_and_country',
                'hint'          => 'উদাহরণ: Azad Traders, Bangladesh',
                'error_message' => 'নঅনুমোদনকারী প্রতিষ্ঠান এবং দেশের নাম পূরণ আবশ্যক ',
                'mandatory'     => false,
            ],
            [
                'field_type'  => 'header',
                "title"       => "ব্যবসা / অফিস - এর ঠিকানা",
                'mandatory'   => false,
                'is_editable' => false,
            ],
            [
                'field_type' => 'multipleView',
                'title'      => '',
                'name'       => 'business_office_address',
                'id'         => 'business_office_address',
                'views'      => addressViews('office'),
                'mandatory'  => true
            ],
            [
                'field_type'  => 'header',
                'title'       => "অন্যান্য তথ্য",
                'mandatory'   => false,
                'is_editable' => false,
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'ভ্যাট রেজিস্ট্রেশন নাম্বার (যদি থাকে)',
                'name'          => 'vat_registration_number',
                'id'            => 'vat_registration_number',
                'hint'          => 'এখানে লিখুন',
                'error_message' => '',
                'mandatory'     => false,
                'input_type'    => 'number'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'প্রতিষ্ঠানের ই-টিন নাম্বার (যদি থাকে)',
                'name'          => 'organization_etin_number',
                'id'            => 'organization_etin_number',
                'hint'          => 'এখানে লিখুন',
                'error_message' => '',
                'mandatory'     => false,
                'input_type'    => 'number'
            ],
            [
                'field_type'    => 'dropdown',
                'title'         => 'প্রতিষ্ঠানের ধরণ',
                'name'          => "organization_type_list",
                'id'            => "organization_type_list",
                'value'         => 'প্রোপ্রাইটরশিপ',
                'hint'          => '',
                'list_type'     => 'dialog',
                'error_message' => 'প্রতিষ্ঠানের ধরণ পূরণ আবশ্যক',
                'mandatory'     => false,
            ],
            [
                'field_type'    => 'dropdown',
                'title'         => 'ব্যবসার ধরণ *',
                'name'          => "business_type_list",
                'id'            => "business_type_list",
                'hint'          => '',
                'list_type'     => 'dialog',
                'error_message' => 'ব্যবসার ধরণ পূরণ আবশ্যক',
                'mandatory'     => true,
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'মাসিক আয়ের পরিমান *',
                'name'          => 'monthly_income',
                'id'            => 'monthly_income',
                'hint'          => 'উদাহরণ: 10000',
                'error_message' => 'মাসিক আয়ের পরিমান পূরণ আবশ্যক',
                'mandatory'     => true,
                'input_type'    => 'number'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'সম্ভব্য মাসিক জমার পরিমান *',
                'name'          => 'total_monthly_deposit',
                'id'            => 'total_monthly_deposit',
                'hint'          => 'উদাহরণ: 10000',
                'error_message' => 'সম্ভব্য মাসিক জমার পরিমান পূরণ আবশ্যক',
                'mandatory'     => true,
                'input_type'    => 'number'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'সম্ভব্য মাসিক উত্তলনের পরিমান *',
                'name'          => 'expected_monthly_withdrew',
                'id'            => 'expected_monthly_withdrew',
                'hint'          => 'উদাহরণ: 10000',
                'error_message' => 'সম্ভব্য মাসিক উত্তলনের পরিমান পূরণ আবশ্যক',
                'mandatory'     => true,
                'input_type'    => 'number'
            ]
        ],
        'nominee'     => [
            [
                'field_type' => 'header',
                'title'      => 'সাধারণ তথ্য',
                'mandatory'  => false
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'নমিনির নাম *',
                'hint'          => 'এখানে লিখুন',
                'name'          => 'nominee_name',
                'id'            => 'nominee_name',
                'error_message' => 'নমিনির নাম পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'date',
                'title'         => 'জন্ম তারিখ *',
                'name'          => 'nominee_birth_date',
                'id'            => 'nominee_birth_date',
                'hint'          => 'উদাহরণ: 01/01/2000',
                'error_message' => 'জন্ম তারিখ পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'আবেদনকারীর সাথে সম্পর্ক *',
                'name'          => 'nominee_relation',
                'id'            => 'nominee_relation',
                'hint'          => 'এখানে লিখুন',
                'error_message' => 'আবেদনকারীর সাথে সম্পর্ক পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'নমিনীর পিতার নাম *',
                'name'          => 'nominee_father_name',
                'id'            => 'nominee_father_name',
                'hint'          => 'এখানে লিখুন',
                'error_message' => 'নমিনীর পিতার নাম পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'নমিনীর মায়ের নাম *',
                'name'          => 'nominee_mother_name',
                'id'            => 'nominee_mother_name',
                'hint'          => 'এখানে লিখুন',
                'error_message' => 'নমিনীর মায়ের নাম পূরণ আবশ্যক'
            ],
            [
                'field_type' => 'conditionalSelect',
                'title'      => 'জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার *',
                'hint'       => 'সিলেক্ট করুন',
                'name'       => 'identification_number_type',
                'id'         => 'identification_number_type',
                'mandatory'  => true,
                'error_message' => 'জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার আবশ্যক',
                'views'      => [
                    [
                        'field_type' => 'editText',
                        'title' => 'জন্ম নিবন্ধন',
                        'name'       => 'birth_certificate_number',
                        'id'         => 'birth_certificate_number',
                        'hint'      => 'জন্ম নিবন্ধন নাম্বার লিখুন',
                        'mandatory'  => true,
                        'error_message' => 'জন্ম নিবন্ধন নাম্বার আবশ্যক',
                    ],
                    [
                        'field_type' => 'editText',
                        'title' => 'পাসপোর্ট',
                        'name'       => 'passport_number',
                        'id'         => 'passport_number',
                        'hint'      => 'পাসপোর্ট নাম্বার লিখুন',
                        'mandatory'  => true,
                        'error_message' => 'পাসপোর্ট নাম্বার আবশ্যক',
                    ],
                    [
                        'field_type' => 'editText',
                        'title'     => 'জাতীয় পরিচয়পত্র',
                        'name'       => 'nid_number',
                        'id'         => 'nid_number',
                        'hint'      => 'জাতীয় পরিচয়পত্র নাম্বার লিখুন',
                        'mandatory'  => true,
                        'error_message' => 'জাতীয় পরিচয়পত্র নাম্বার আবশ্যক',
                    ]
                ]
            ],
//            [
//                'field_type'    => 'editText',
//                'title'         => 'জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার *',
//                'name'          => 'identification_number',
//                'id'            => 'identification_number',
//                'hint'          => 'এখানে লিখুন',
//                'error_message' => 'জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার পূরণ আবশ্যক'
//            ],
//            [
//                'field_type' => 'radioGroup',
//                'title'      => '',
//                'name'       => 'identification_number_type',
//                'id'         => 'identification_number_type',
//                'mandatory'  => false,
//                'views'      => [
//                    [
//                        'field_type' => 'radioButton',
//                        'name'       => 'birth_certificate_number',
//                        'id'         => 'birth_certificate_number',
//                        'title'      => 'জন্ম নিবন্ধন নাম্বার',
//                        'mandatory'  => false,
//                        'value'      => 0
//                    ],
//                    [
//                        'field_type' => 'radioButton',
//                        'name'       => 'passport_number',
//                        'id'         => 'passport_number',
//                        'title'      => 'পাসপোর্ট',
//                        'mandatory'  => false,
//                        'value'      => 0
//                    ],
//                    [
//                        'field_type' => 'radioButton',
//                        'name'       => 'nid_number',
//                        'id'         => 'nid_number',
//                        'title'      => 'জাতীয় পরিচয়পত্র',
//                        'mandatory'  => false,
//                        'value'      => 0
//                    ]
//                ]
//            ],
            [
                'field_type' => 'header',
                'title'      => 'নমিনির স্থায়ী ঠিকানা ',
                'mandatory'  => false
            ],
            [
                'field_type' => 'multipleView',
                'title'      => '',
                'name'       => 'nominee_permanent_address',
                'id'         => 'nominee_permanent_address',
                'views'      => addressViews('nominee', 'Bangladesh'),
                'mandatory'  => false
            ],
            [
                'field_type' => 'header',
                'title'      => 'নমিনির অভিভাবকের তথ্য (নমিনির বয়স  যদি ১৮ বছরের নিচে হয়)',
                'mandatory'  => false
            ],
            [
                'field_type' => 'editText',
                'title'      => 'অভিভাবক (নমিনি যদি ১৮ বছরের নিচে হয়) ',
                'name'       => 'nominee_guardian',
                'id'         => 'nominee_guardian',
                'hint'       => 'এখানে লিখুন',
                'mandatory'  => false
            ],
            [
                'field_type' => 'editText',
                'title'      => 'অভিভাবকের জাতীয় পরিচয়পত্রের নাম্বার ',
                'name'       => 'nominee_guardian_nid',
                'id'         => 'nominee_guardian_nid',
                'hint'       => 'এখানে লিখুন',
                'mandatory'  => false,
                'input_type' => 'number'
            ],
            [
                'field_type' => 'header',
                'title'      => 'নমিনির অভিভাবকের স্থায়ী ঠিকানা ',
                'mandatory'  => false
            ],
            [
                'field_type' => 'multipleView',
                'title'      => '',
                'name'       => 'nominee_guardian_address',
                'id'         => 'nominee_guardian_address',
                'mandatory'  => false,
                'views'      => [
                    [
                        'field_type'    => 'editText',
                        'title'         => 'স্ট্রিট নং / গ্রামের নাম',
                        'name'          => 'street_village_nominee_guardian_address',
                        'id'            => 'street_village_nominee_guardian_address',
                        'hint'          => 'স্ট্রিট নং / গ্রামের নাম',
                        'error_message' => 'স্ট্রিট নং / গ্রামের নাম  পূরণ আবশ্যক',
                        'mandatory'     => false
                    ],
                    [
                        'field_type'    => 'editText',
                        'title'         => 'পোস্ট কোড',
                        'name'          => 'postcode_nominee_guardian_address',
                        'id'            => 'postcode_nominee_guardian_address',
                        'hint'          => 'পোস্ট কোড',
                        'error_message' => 'পোস্ট কোড  পূরণ আবশ্যক',
                        'mandatory'     => false,
                        'input_type'    => 'number'
                    ],
                    [
                        'field_type'    => 'dropdown',
                        'title'         => 'জেলা ',
                        'name'          => 'district_nominee_guardian_address',
                        'id'            => 'district_nominee_guardian_address',
                        'hint'          => 'জেলা',
                        'list_type'     => 'new_page_radio',
                        'error_message' => 'জেলার নাম পূরণ আবশ্যক',
                        'mandatory'     => false
                    ],
                    [
                        'field_type'    => 'dropdown',
                        'title'         => 'থানা / উপজেলা',
                        'list_type'     => 'new_page_radio',
                        'name'          => 'sub_district_nominee_guardian_address',
                        'id'            => 'sub_district_nominee_guardian_address',
                        'hint'          => 'থানা / উপজেলা',
                        'error_message' => 'থানা / উপজেলা নাম পূরণ আবশ্যক',
                        'mandatory'     => false
                    ],
                    [
                        'field_type'    => 'editText',
                        'title'         => 'দেশ ',
                        'list_type'     => 'new_page_radio',
                        'name'          => 'country_nominee_guardian_address',
                        'id'            => 'country_nominee_guardian_address',
                        'hint'          => 'দেশ',
                        'error_message' => 'দেশের নাম পূরণ আবশ্যক',
                        'mandatory'     => false
                    ]
                ]
            ],

        ],
        'account'     => [
            [
                'field_type'    => 'editText',
                'title'         => 'অ্যাকাউন্টের নাম',
                'hint'          => 'ABUL KALAM AZAD',
                'name'          => 'applicant_name',
                'id'            => 'applicant_name',
                'error_message' => 'আবেদনকারীর নাম পূরণ আবশ্যক',
                'mandatory'     => false,
                'is_editable'   => false
            ],
            [
                'field_type' => 'radioGroup',
                'title'      => 'অ্যাকাউন্টের ধরণ *',
                'name'       => 'type_of_account',
                'value'      => '',
                'id'         => 'type_of_account',
                'views'      => [
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'account_savings',
                        'id'         => 'account_savings',
                        'title'      => 'Savings',
                        'mandatory'  => false,
                        'value'      => 1
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'account_current',
                        'id'         => 'account_current',
                        'title'      => 'Current',
                        'mandatory'  => false,
                        'value'      => 0
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'account_snd',
                        'id'         => 'account_snd',
                        'title'      => 'SND',
                        'mandatory'  => false,
                        'value'      => 0
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'account_fc',
                        'id'         => 'account_fc',
                        'title'      => 'FC',
                        'mandatory'  => false,
                        'value'      => 0
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'account_erq',
                        'id'         => 'account_erq',
                        'title'      => 'ERQ',
                        'mandatory'  => false,
                        'value'      => 0
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'account_others',
                        'id'         => 'account_others',
                        'title'      => 'Others',
                        'mandatory'  => false,
                        'value'      => 0
                    ],
                ]
            ],
            [
                'field_type'    => 'dropdown',
                'title'         => 'ব্রাঞ্চ ',
                'list_type'     => 'same_page_radio',
                'name'          => 'branch',
                'id'            => 'branch',
                'hint'          => 'ব্রাঞ্চ ',
                'error_message' => 'ব্রাঞ্চ নাম পূরণ আবশ্যক',
                'mandatory'     => false
            ],
            [
                'field_type' => 'radioGroup',
                'title'      => 'মূদ্রা ',
                'name'       => 'money_type',
                'id'         => 'money_type',
                'value'      => '',
                'mandatory'  => false,
                'views'      => [
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'money_taka',
                        'id'         => 'money_taka',
                        'title'      => 'টাকা',
                        'mandatory'  => false,
                        'value'      => 1
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'money_dollar',
                        'id'         => 'money_dollar',
                        'title'      => 'ডলার',
                        'mandatory'  => false,
                        'value'      => 0
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'money_euro',
                        'id'         => 'money_euro',
                        'title'      => 'ইউরো',
                        'mandatory'  => false,
                        'value'      => 0
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'money_pound',
                        'id'         => 'money_pound',
                        'title'      => 'পাউন্ড',
                        'mandatory'  => false,
                        'value'      => 0
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'money_others',
                        'id'         => 'money_others',
                        'title'      => 'অন্যান্য',
                        'mandatory'  => false,
                        'value'      => 0
                    ]
                ]
            ],
            [
                'field_type' => 'radioGroup',
                'title'      => 'অপারেশনের ধরণ ',
                'name'       => 'type_of_operation',
                'id'         => 'type_of_operation',
                'value'      => 'অপারেশনের ধরণ ',
                'mandatory'  => false,
                'views'      => [
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'operation_individual',
                        'id'         => 'operation_individual',
                        'title'      => 'Individual',
                        'mandatory'  => false,
                        'value'      => 1
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'operation_joint',
                        'id'         => 'operation_joint',
                        'title'      => 'Joint',
                        'mandatory'  => false,
                        'value'      => 0
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'operation_others',
                        'id'         => 'operation_others',
                        'title'      => 'Others',
                        'mandatory'  => false,
                        'value'      => 0
                    ]
                ]
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'প্রাথমিক জমা',
                'hint'          => 'উদাহরণ: 5000',
                'name'          => 'first_deposit',
                'id'            => 'first_deposit',
                'error_message' => '',
                'mandatory'     => false,
                'input_type'    => 'number'
            ],
            [
                'field_type' => 'multipleView',
                'title'      => 'চেক বই নিতে চান?',
                'name'       => 'check_book',
                'id'         => 'check_book',
                'value'      => 'চেক বই নিতে চান?',
                'mandatory'  => false,
                'views'      => booleanView('check_book')
            ],
            [
                'field_type' => 'multipleView',
                'title'      => 'ই-স্টেটমেন্ট',
                'name'       => 'e_payment',
                'id'         => 'e_payment',
                'value'      => 'ই-স্টেটমেন্ট',
                'mandatory'  => false,
                'views'      => booleanView('e_payment')
            ],
            [
                'field_type' => 'multipleView',
                'title'      => 'ইন্টারনেট ব্যাংকিং ',
                'name'       => 'internet_banking',
                'id'         => 'internet_banking',
                'value'      => 'ইন্টারনেট ব্যাংকিং ',
                'mandatory'  => false,
                'views'      => booleanView('internet_banking')
            ]
        ],
        'documents'   => [
            [
                'field_type'    => 'imageDocument',
                "input_type"    => "image",
                'title'         => 'ট্রেড লাইসেন্স *',
                'hint'          => 'ট্রেড লাইসেন্স',
                'name'          => 'trade_licence_document',
                'id'            => 'trade_licence_document',
                'error_message' => 'ট্রেড লাইসেন্সের ছবি দেয়া আবশ্যক',
                "mandatory"     => true
            ],
            [
                'field_type'    => 'imageDocument',
                "input_type"    => "image",
                'title'         => 'কোম্পানির লেটার-হেড প্যাড',
                'hint'          => 'কোম্পানির লেটার-হেড প্যাড',
                'name'          => 'company_letter_head',
                'id'            => 'company_letter_head',
                'error_message' => 'কোম্পানির লেটার-হেড প্যাড এর ছবি দেয়া আবশ্যক',
                "mandatory"     => false
            ],
            [
                'field_type'    => 'imageDocument',
                "input_type"    => "image",
                'title'         => 'ট্রেড সিল',
                'hint'          => 'ট্রেড সিল',
                'name'          => 'trade_seal_document',
                'id'            => 'trade_seal_document',
                'error_message' => 'ট্রেড সিল এর ছবি দেয়া আবশ্যক',
                "mandatory"     => false
            ],
            [
                'field_type'    => 'imageDocument',
                "input_type"    => "image",
                'title'         => 'ই-টিন *',
                'hint'          => 'ই-টিন',
                'name'          => 'e_tin_document',
                'id'            => 'e_tin_document',
                'error_message' => 'ই-টিন এর ছবি দেয়া আবশ্যক',
                "mandatory"     => true
            ],
            [
                'field_type'    => 'imageDocument',
                "input_type"    => "image",
                'title'         => 'ভ্যাট রেজিস্ট্রেশন',
                'hint'          => 'ভ্যাট রেজিস্ট্রেশন',
                'name'          => 'vat_registration_document',
                'id'            => 'vat_registration_document',
                'error_message' => 'ভ্যাট রেজিস্ট্রেশন এর ছবি দেয়া আবশ্যক',
                "mandatory"     => false
            ],
            [
                'field_type'    => 'imageDocument',
                "input_type"    => "image",
                'title'         => 'রেন্টাল এগ্রিমেন্ট (যদি থাকে)',
                'hint'          => 'রেন্টাল এগ্রিমেন্ট',
                'name'          => 'rental_agreement_document',
                'id'            => 'rental_agreement_document',
                'error_message' => 'রেন্টাল এগ্রিমেন্ট এর ছবি দেয়া আবশ্যক',
                "mandatory"     => false
            ],
            [
                'field_type'    => 'imageDocument',
                "input_type"    => "image",
                'title'         => 'পানি / বিদ্যুৎ / গ্যাস / টেলিফোন বিল *',
                'hint'          => 'পানি / বিদ্যুৎ / গ্যাস / টেলিফোন বিল',
                'name'          => 'bill_document',
                'id'            => 'bill_document',
                'error_message' => 'পানি / বিদ্যুৎ / গ্যাস / টেলিফোন বিল এর ছবি দেয়া আবশ্যক',
                "mandatory"     => true
            ]
        ],
        'nid_selfie'  => [
            [
                'field_type'  => 'header',
                'is_editable' => false,
                'title'       => 'যাচাইকৃত NID তথ্য',
                'mandatory'  => false,
            ],
            [
                'field_type'  => 'text',
                'name'        => 'nid_no',
                'is_editable' => false,
                'title'       => 'NID নাম্বার'
            ],
            [
                'field_type'  => 'text',
                'name'        => 'applicant_name_ben',
                'is_editable' => false,
                'title'       => 'নাম'
            ],
            [
                'field_type'  => 'text',
                'name'        => 'father_name',
                'is_editable' => false,
                'title'       => 'পিতার নাম'
            ],
            [
                'field_type'  => 'text',
                'name'        => 'mother_name',
                'is_editable' => false,
                'title'       => 'মাতার নাম'
            ],
            [
                'field_type'  => 'text',
                'input_name'  => 'dob',
                'name'        => 'dob',
                'is_editable' => false,
                'title'       => 'জন্ম তারিখ'
            ],
            [
                'field_type'  => 'text',
                'name'        => 'pres_address',
                'is_editable' => false,
                'title'       => 'ঠিকানা'
            ]
        ]
    ],
    'gigatech_liveliness_sdk_auth_token' => env('GIGATECH_LIVELINESS_SDK_AUTH_TOKEN'),
    'default_prime_bank_account'         => [
        "type_of_account"   => [
            "account_savings" => "1",
            "account_current" => "0",
            "account_snd"     => "0",
            "account_fc"      => "0",
            "account_erq"     => "0",
            "account_others"  => "0"
        ],
        "money_type"        => [
            "money_taka"   => "1",
            "money_dollar" => "0",
            "money_euro"   => "0",
            "money_pound"  => "0",
            "money_others" => "0"
        ],
        "type_of_operation" => [
            "operation_individual" => "1",
            "operation_joint"      => "0",
            "operation_others"     => "0"
        ],
        "check_book"        => [
            "check_book_yes" => "1",
            "check_book_no"  => "0"
        ],
        "e_payment"         => [
            "e_payment_yes" => "1",
            "e_payment_no"  => "0"
        ],
        "internet_banking"  => [
            "internet_banking_yes" => "1",
            "internet_banking_no"  => "0"
        ]
    ],
    'cpv_pending_account_null_message'   => 'আপনার ব্যঙ্ক একাউন্ট ওপেনিং আবেদন প্রক্রিয়াধিন রয়েছে। আগামী ৪৮ ঘন্টার মধ্যে একাউন্ট ওপেনিং সম্মপন্ন হবে, অনুগ্রহ করে অপক্ষা করুন অথবা বিস্তারিত জানতে কল করুন ১৬৫১৬',
    'cpv_pending_message'                => 'এই মুহূর্তে আপনার অ্যাকাউন্টে শুধু মাত্র টাকা জমা দেয়া যাবে। সম্পূর্ণরূপে অ্যাকাউন্ট সচল করতে আপনার নির্ধারিত শাখায় গিয়ে স্বাক্ষর করুন এবং আপনার ঠিকানা ভেরিফিকেশন এর জন্য অপেক্ষা করুন। ',
    'cpv_unverified_message'             => 'আপনার প্রদত্ত ঠিকানা ভেরিফিকেশন করা সম্ভব হয়নি। পূর্ণাঙ্গ ব্যাংক অ্যাকাউন্ট সচল করতে ১৬৫১৬ এ কল করে সঠিন তথ্য দিয়ে পুনরায় ঠিকানা ভেরিফিকেশন এর জন্য অনুরোধ করুন।',
    'signed_verified_message'            => 'আভিনন্দন! প্রাইম ব্যঙ্ক এ সফল ভাবে আপনার অ্যাকাউন্ট খোলা হয়েছে। এখন থেকে আপনি সকল ধরনের লেনদেন করতে পারবেন।',
    'unsigned_message'                   => 'পূর্ণাঙ্গ ব্যাংক অ্যাকাউন্ট সচল করতে আপনার নির্ধারিত প্রাইম ব্যাংক এর ব্রাঞ্চ এ গিয়ে স্বাক্ষর করুন',
    'message_type'                       => [
        'cpv_pending'      => 'Warning',
        'cpv_unverified'   => 'Error',
        'cpv_verified'     => 'Success',
        'unsigned_message' => 'Info'
    ],

    "PBL_account_create_key" => "6b9580a51b1a26026b50ceeb84dkc93ofcic6f7idcvl3jl6j"
];
