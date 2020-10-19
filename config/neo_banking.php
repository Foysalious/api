<?php
function addressViews($type)
{
    return [
        [
            'field_type'    => 'editText',
            'title'         => 'স্ট্রিট নং / গ্রামের নাম *',
            'name'          => 'street_village_' . $type . '_address',
            'hint'          => '',
            'error_message' => 'স্ট্রিট নং / গ্রামের নাম  পূরণ আবশ্যক'
        ],
        [
            'field_type'    => 'editText',
            'title'         => 'পোস্ট কোড *',
            'name'          => 'postcode_' . $type . '_address',
            'hint'          => '',
            'error_message' => 'পোস্ট কোড  পূরণ আবশ্যক'
        ],
        [
            'field_type'    => 'dropdown',
            'title'         => 'জেলা *',
            'name'          => 'district_' . $type . '_address',
            'hint'          => '',
            'list_type'     => 'new_page_radio',
            'error_message' => 'জেলার নাম পূরণ আবশ্যক'
        ],
        [
            'field_type'    => 'dropdown',
            'title'         => 'থানা / উপজেলা *',
            'list_type'     => 'new_page_radio',
            'name'          => 'sub_district_' . $type . '_address',
            'hint'          => '',
            'error_message' => 'থানা / উপজেলা নাম পূরণ আবশ্যক'
        ],
        [
            'field_type'    => 'dropdown',
            'title'         => 'দেশ *',
            'list_type'     => 'new_page_radio',
            'name'          => 'country_' . $type . '_address',
            'hint'          => '',
            'error_message' => 'দেশের নাম পূরণ আবশ্যক'
        ]
    ];
}

return [
    'prime_bank_sbs_url' => env('PRIME_BANK_SBS_URL'),
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
                'error_message' => 'আবেদনকারীর নাম পূরণ আবশ্যক',
                'is_editable'   => false
            ],
            [
                'field_type'    => 'date',
                'title'         => 'জন্ম তারিখ *',
                'name'          => 'birth_date',
                'hint'          => 'উদাহরণ: 01/01/2000',
                'error_message' => 'জন্ম তারিখ  পূরণ আবশ্যক',
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'বাবার নাম  *',
                'name'          => 'father_name',
                'hint'          => 'ABUL KALAM',
                'error_message' => 'বাবার নাম পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'মায়ের নাম  *',
                'name'          => 'mother_name',
                'hint'          => 'Mrs. ABUL',
                'error_message' => 'মায়ের নাম পূরণ আবশ্যক'
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
                'title'         => 'পেশা *',
                'name'          => 'occupation_name',
                'hint'          => 'Farmer',
                'error_message' => 'পেশার ধরণ পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'প্রতিষ্ঠান এর নাম লিখুন',
                'name'          => 'company_name',
                'hint'          => 'Sheba.xyz',
                'error_message' => 'প্রতিষ্ঠান এর নাম পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'ই-টিন নাম্বার  *',
                'name'          => 'etin_number',
                'hint'          => '4654453',
                'error_message' => 'ই-টিন নাম্বার পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার  *',
                'name'          => 'nid_passport_birth_cer_number',
                'hint'          => '654564544645464',
                'error_message' => 'জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার পূরণ আবশ্যক'
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
                'views'      => addressViews('present'),
                'mandatory'  => false
            ],
            [
                'field_type' => 'multipleView',
                'title'      => '',
                'name'       => 'present_address',
                'mandatory'  => false,
                'views'      => [
                    [
                        'field_type' => 'checkbox',
                        'name'       => 'present_permanent_same_address_checked',
                        'value'      => 0
                    ],
                    [
                        'field_type' => 'textView',
                        'title'      => 'বর্তমান ঠিকানা এবং স্থায়ী ঠিকানা একই',
                        'name'       => 'present_permanent_same_address_text',
                        'mandatory'  => false
                    ]
                ]
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
                'mandatory'  => false,
                'views'      => addressViews('permanent')
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
                'hint'          => '+880 1678242900',
                'name'          => 'mobile',
                'error_message' => "মোবাইল নাম্বার পূরণ আবশ্যক",
                'is_editable'   => false
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'ই-মেইল আইডি',
                'name'          => 'email',
                'hint'          => 'arafat@gmail.com',
                'error_message' => 'ই-মেইল আইডি পূরণ আবশ্যক',
                'mandatory'     => false
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'আপনার প্রতিষ্ঠানের নাম (বড় অক্ষরে) *',
                'name'          => 'company_name',
                'hint'          => 'AZAD TELECOM',
                'error_message' => 'প্রতিষ্ঠানের নাম  পূরণ আবশ্যক'
            ],
            [
                'field_type'  => 'header',
                "title"       => "ট্রেড লাইসেন্স সম্পর্কিত তথ্য",
                'mandatory'   => false,
                'is_editable' => false,
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'সট্রেড লাইসেন্স নং *',
                'name'          => 'trade_licence_number',
                'hint'          => 'উদাহরণ: AHMED TELECOM',
                'error_message' => 'ট্রেড লাইসেন্স নং পূরণ আবশ্যক'

            ],
            [
                'field_type'    => 'date',
                'title'         => 'নিবন্ধনের তারিখ *',
                'name'          => 'trade_licence_date',
                'hint'          => 'উদাহরণ: 01/01/2000',
                'error_message' => 'টনিবন্ধনের তারিখ  পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => "অনুমোদনকারী প্রতিষ্ঠান *",
                'name'          => 'grantor_organization',
                'hint'          => 'Sheba.xyz',
                'error_message' => 'পঅনুমোদনকারী প্রতিষ্ঠানের নাম পূরণ আবশ্যক '
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
                'hint'          => '90145',
                'error_message' => "রেজিস্ট্রেশন নং পূরণ আবশ্যক",
                'mandatory'     => false,
            ],
            [
                'field_type'    => 'date',
                'title'         => 'নিবন্ধনের তারিখ ',
                'name'          => 'registration_date',
                'hint'          => 'উদাহরণ: 01/01/2000',
                'error_message' => 'নিবন্ধনের তারিখ  পূরণ আবশ্যক"',
                'mandatory'     => false,
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'অনুমোদনকারী প্রতিষ্ঠান এবং দেশ',
                'name'          => 'grantor_organization_and_country',
                'hint'          => 'উদাহরণ: Sheba Platform Limited, Bangladesh',
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
                'views'      => addressViews('office'),
                'mandatory'  => false
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
                'hint'          => 'এখানে লিখুন',
                'error_message' => '',
                'mandatory'     => false,
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'প্রতিষ্ঠানের ই-টিন নাম্বার (যদি থাকে)',
                'name'          => 'organization_etin_number',
                'hint'          => 'এখানে লিখুন',
                'error_message' => '',
                'mandatory'     => false,
            ],
            [
                'field_type'    => 'dropdown',
                'title'         => 'প্রতিষ্ঠানের ধরণ',
                'name'          => "organization_type_list",
                'hint'          => '',
                'list_type'     => 'new_page_radio',
                'error_message' => 'প্রতিষ্ঠানের ধরণ পূরণ আবশ্যক',
                'mandatory'     => false,
            ],
            [
                'field_type'    => 'dropdown',
                'title'         => 'ব্যবসার ধরণ',
                'name'          => "business_type_list",
                'hint'          => '',
                'list_type'     => 'new_page_radio',
                'error_message' => 'ব্যবসার ধরণ পূরণ আবশ্যক',
                'mandatory'     => false,
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'বাৎসরিক আয়ের পরিমান',
                'name'          => 'yearly_earning',
                'hint'          => 'উদাহরণ: 10000',
                'error_message' => 'বাৎসরিক আয়ের পরিমান পূরণ আবশ্যক',
                'mandatory'     => false,
            ],
        ],
        'nominee'     => [
            [
                'field_type' => 'header',
                'title'      => 'সাধারণ তথ্য',
                'mandatory'  => false,
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'নমিনির নাম *',
                'hint'          => 'এখানে লিখুন',
                'name'          => 'nominee_name',
                'error_message' => 'নমিনির নাম পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'date',
                'title'         => 'জন্ম তারিখ *',
                'name'          => 'nominee_birth_date',
                'hint'          => 'উদাহরণ: 01/01/2000',
                'error_message' => 'জন্ম তারিখ  পূরণ আবশ্যক',
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'আবেদনকারীর সাথে সম্পর্ক *',
                'name'          => 'nominee_relation',
                'hint'          => 'এখানে লিখুন',
                'error_message' => 'আবেদনকারীর সাথে সম্পর্ক পূরণ আবশ্যক'
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার *',
                'name'          => 'identification_number',
                'hint'          => 'এখানে লিখুন',
                'error_message' => 'জাতীয় পরিচয়পত্র/পাসপোর্ট/জন্ম নিবন্ধন নাম্বার পূরণ আবশ্যক'
            ],
            [
                'field_type' => 'multipleView',
                'title'      => '',
                'name'       => 'identification_number_type',
                'mandatory'  => false,
                'views'      => [
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'birth_certificate_number',
                        'title'      => 'জন্ম নিবন্ধন নাম্বার',
                        'mandatory'  => false
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'passport_number',
                        'title'      => 'পাসপোর্ট',
                        'mandatory'  => false
                    ],
                    [
                        'field_type' => 'radioButton',
                        'name'       => 'nid_number',
                        'title'      => 'পাসপোর্ট',
                        'mandatory'  => false
                    ]
                ]
            ],
            [
                'field_type' => 'header',
                'title'      => 'নমিনির স্থায়ী ঠিকানা ',
                'mandatory'  => false
            ],
            [
                'field_type' => 'multipleView',
                'title'      => '',
                'name'       => 'nominee_permanent_address',
                'views'      => addressViews('nominee'),
                'mandatory'  => false
            ],
            [
                'field_type' => 'header',
                'title'      => 'নমিনির অভিভাবকের তথ্য (নমিনির বয়স  যদি ১৮ বছরের নিচে হয়)',
                'mandatory'  => false
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'অভিভাবক (নমিনি যদি ১৮ বছরের নিচে হয়) ',
                'name'          => 'nominee_guardian',
                'hint'          => 'এখানে লিখুন',
                'mandatory'     => false
            ],
            [
                'field_type'    => 'editText',
                'title'         => 'অভিভাবকের জাতীয় পরিচয়পত্রের নাম্বার ',
                'name'          => 'nominee_guardian_nid',
                'hint'          => 'এখানে লিখুন',
                'mandatory'     => false
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
                'mandatory'  => false,
                'views'      => [
                    [
                        'field_type'    => 'editText',
                        'title'         => 'স্ট্রিট নং / গ্রামের নাম *',
                        'name'          => 'street_village_nominee_guardian_address',
                        'hint'          => '',
                        'error_message' => 'স্ট্রিট নং / গ্রামের নাম  পূরণ আবশ্যক',
                        'mandatory'     => false
                    ],
                    [
                        'field_type'    => 'editText',
                        'title'         => 'পোস্ট কোড *',
                        'name'          => 'postcode_nominee_guardian_address',
                        'hint'          => '',
                        'error_message' => 'পোস্ট কোড  পূরণ আবশ্যক',
                        'mandatory'     => false
                    ],
                    [
                        'field_type'    => 'dropdown',
                        'title'         => 'জেলা *',
                        'name'          => 'district_nominee_guardian_address',
                        'hint'          => '',
                        'list_type'     => 'new_page_radio',
                        'error_message' => 'জেলার নাম পূরণ আবশ্যক'
                    ],
                    [
                        'field_type'    => 'dropdown',
                        'title'         => 'থানা / উপজেলা *',
                        'list_type'     => 'new_page_radio',
                        'name'          => 'sub_district_nominee_guardian_address',
                        'hint'          => '',
                        'error_message' => 'থানা / উপজেলা নাম পূরণ আবশ্যক'
                    ],
                    [
                        'field_type'    => 'dropdown',
                        'title'         => 'দেশ *',
                        'list_type'     => 'new_page_radio',
                        'name'          => 'country_nominee_guardian_address',
                        'hint'          => '',
                        'error_message' => 'দেশের নাম পূরণ আবশ্যক',
                        'mandatory'     => false
                    ]
                ]
            ],

        ]
    ],
    'gigatech_liveliness_sdk_auth_token' => env('GIGATECH_LIVELINESS_SDK_AUTH_TOKEN')
];
