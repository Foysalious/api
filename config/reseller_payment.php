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
    "category_form_items" => [
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
    ],

];