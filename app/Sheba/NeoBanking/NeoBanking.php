<?php

namespace Sheba\NeoBanking;

class NeoBanking
{
    private $bank;
    private $partner;
    private $resource;

    public function __construct()
    {
    }

    public function setBank($bank)
    {
        $this->bank = $bank;
        return $this;
    }

    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function organizationInformation()
    {
        return [
            "organization_information" => [
                "communication_info" =>[
                    "field_type" => "header",
                    "title"      => "যোগাযোগ এর তথ্য",
                ],
                "mobile" => [
                    "field_type"    => "viewText",
                    "input_type"    => "text",
                    "name"          => "mobile",
                    "id"            => "mobile",
                    "value"         => null,
                    "title"         => "মোবাইল নাম্বার",
                    "hint"          => "+880 1678242900",
                    "error_message" => "This field is required",
                    "mandatory"     => true,
                    "is_editable"   => false
                ],
                "email" => [
                    "field_type"    => "editText",
                    "input_type"    => "email",
                    "name"          => "email",
                    "id"            => "email",
                    "value"         => null,
                    "title"         => "ই-মেইল আইডি",
                    "hint"          => "arafat@gmail.com",
                    "error_message" => "",
                    "mandatory"     => false,
                    "is_editable"   => true
                ],
                "company_name" => [
                    "field_type"    => "editText",
                    "input_type"    => "text",
                    "name"          => "company_name",
                    "id"            => "company_name",
                    "value"         => null,
                    "title"         => "আপনার প্রতিষ্ঠানের নাম (বড় অক্ষরে)",
                    "hint"          => "AZAD TELECOM",
                    "error_message" => "This field is required",
                    "mandatory"     => true,
                    "is_editable"   => true
                ],
                "licence_info" => [
                    "field_type" => "header",
                    "title"      => "যোগাযোগ এর তথ্য",
                ],
                "trade_licence_number" => [
                    "field_type"    => "editText",
                    "input_type"    => "text",
                    "name"          => "trade_licence_number",
                    "id"            => "trade_licence_number",
                    "value"         => null,
                    "title"         => "ট্রেড লাইসেন্স নং",
                    "hint"          => "উদাহরণ: AHMED TELECOM",
                    "error_message" => "This field is required",
                    "mandatory"     => true,
                    "is_editable"   => true
                ],
                "trade_licence_date" => [
                    "field_type"    => "editText",
                    "input_type"    => "date",
                    "name"          => "trade_licence_date",
                    "id"            => "trade_licence_date",
                    "value"         => null,
                    "title"         => "নিবন্ধনের তারিখ",
                    "hint"          => "উদাহরণ: 01/01/2000",
                    "error_message" => "This field is required",
                    "mandatory"     => true,
                    "is_editable"   => true
                ],
                "grantor_organization" => [
                    "field_type"    => "editText",
                    "input_type"    => "text",
                    "name"          => "grantor_organization",
                    "id"            => "grantor_organization",
                    "value"         => null,
                    "title"         => "অনুমোদনকারী প্রতিষ্ঠান",
                    "hint"          => "উদাহরণ: 5000",
                    "error_message" => "This field is required",
                    "mandatory"     => true,
                    "is_editable"   => true
                ],
                "registration_info" => [
                    "field_type" => "header",
                    "title"      => "রেজিস্ট্রেশন সম্পর্কিত তথ্য",
                ],
                "registration_number" => [
                    "field_type"    => "editText",
                    "input_type"    => "text",
                    "name"          => "registration_number",
                    "id"            => "registration_number",
                    "value"         => null,
                    "title"         => "রেজিস্ট্রেশন নং",
                    "hint"          => "এখানে লিখুন",
                    "error_message" => "",
                    "mandatory"     => false,
                    "is_editable"   => true
                ],
                "registration_date" => [
                    "field_type"    => "editText",
                    "input_type"    => "date",
                    "name"          => "registration_date",
                    "id"            => "registration_date",
                    "value"         => null,
                    "title"         => "নিবন্ধনের তারিখ",
                    "hint"          => "উদাহরণ: 01/01/2000",
                    "error_message" => "",
                    "mandatory"     => false,
                    "is_editable"   => true
                ],
                "applier_organization_country" => [
                    "field_type"    => "editText",
                    "input_type"    => "text",
                    "name"          => "applier_organization_country",
                    "id"            => "applier_organization_country",
                    "value"         => null,
                    "title"         => "অনুমোদনকারী প্রতিষ্ঠান এবং দেশ",
                    "hint"          => "এখানে লিখুন",
                    "error_message" => "",
                    "mandatory"     => false,
                    "is_editable"   => true
                ],
                "business_location" => [
                    "field_type" => "header",
                    "title"      => "ব্যাবসা / অফিস - এর ঠিকানা",
                ],
                "street_or_village_and_postcode" => [
                    "field_type"    => "doubleView",
                    "name"          => "street_or_village_and_postcode",
                    "id"            => "street_or_village_and_postcode",
                    "views"         => [
                        "street_or_village" => [
                            "field_type"    => "editText",
                            "input_type"    => "text",
                            "name"          => "street_or_village",
                            "id"            => "street_or_village",
                            "value"         => null,
                            "title"         => "স্ট্রিট নং / গ্রামের নাম",
                            "hint"          => "এখানে লিখুন",
                            "error_message" => "This field is required",
                            "mandatory"     => true,
                            "is_editable"   => true
                        ],
                        "postcode" => [
                            "field_type"    => "editText",
                            "input_type"    => "text",
                            "name"          => "postcode",
                            "id"            => "postcode",
                            "value"         => null,
                            "title"         => "পোস্ট কোড",
                            "hint"          => "এখানে লিখুন",
                            "error_message" => "This field is required",
                            "mandatory"     => true,
                            "is_editable"   => true
                        ]
                    ]
                ],
                "district_and_subdistrict" => [
                    "field_type"    => "doubleView",
                    "name"          => "street_or_village_and_postcode",
                    "id"            => "street_or_village_and_postcode",
                    "views"         => [
                        "district" => [
                            "field_type"    => "dropdown",
                            "list_type"     => "dialog",
                            "name"          => "district",
                            "id"            => "district",
                            "value"         => null,
                            "title"         => "জেলা",
                            "hint"          => "সিলেক্ট করুন",
                            "error_message" => "This field is required",
                            "mandatory"     => true,
                            "is_editable"   => true,
                            "list"          => [
                                [
                                    "key"=> "gaibandha",
                                    "en" => "Gaibandha",
                                    "bn" => "গাইবান্ধা"
                                ],
                                [
                                    "key" => "dhaka",
                                    "en"  => "Dhaka",
                                    "bn"  => "ঢাকা"
                                ]
                            ]
                        ],
                        "subdistrict" => [
                            "field_type"    => "dropdown",
                            "list_type"     => "dialog",
                            "name"          => "subdistrict",
                            "id"            => "subdistrict",
                            "value"         => null,
                            "title"         => "পোস্ট কোড",
                            "hint"          => "এখানে লিখুন",
                            "error_message" => "This field is required",
                            "mandatory"     => true,
                            "is_editable"   => true,
                            "list"          => [
                                [
                                    "key"=> "gaibandha",
                                    "en" => "Gaibandha",
                                    "bn" => "গাইবান্ধা"
                                ],
                                [
                                    "key" => "dhaka",
                                    "en"  => "Dhaka",
                                    "bn"  => "ঢাকা"
                                ]
                            ]
                        ]
                    ]
                ],
                "business_country" => [
                    "field_type"    => "doubleView",
                    "name"          => "business_country",
                    "id"            => "business_country",
                    "views"         => [
                        "business_country" => [
                            "field_type"    => "dropdown",
                            "list_type"     => "dialog",
                            "name"          => "business_country",
                            "id"            => "business_country",
                            "value"         => null,
                            "title"         => "দেশ",
                            "hint"          => "এখানে লিখুন",
                            "error_message" => "This field is required",
                            "mandatory"     => true,
                            "is_editable"   => true
                        ]
                    ]
                ],
                "other_information" => [
                    "field_type" => "header",
                    "title"      => "অন্যান্য তথ্য",
                ],
                "vat_registration_number" => [
                    "field_type"    => "editText",
                    "input_type"    => "text",
                    "name"          => "vat_registration_number",
                    "id"            => "vat_registration_number",
                    "value"         => null,
                    "title"         => "ভ্যাট রেজিস্ট্রেশন নাম্বার (যদি থাকে)",
                    "hint"          => "এখানে লিখুন",
                    "error_message" => "",
                    "mandatory"     => false,
                    "is_editable"   => true
                ],
                "organization_etin_number" => [
                    "field_type"    => "editText",
                    "input_type"    => "text",
                    "name"          => "organization_etin_number",
                    "id"            => "organization_etin_number",
                    "value"         => null,
                    "title"         => "প্রতিষ্ঠানের ই-টিন নাম্বার (যদি থাকে)",
                    "hint"          => "এখানে লিখুন",
                    "error_message" => "",
                    "mandatory"     => false,
                    "is_editable"   => true
                ],
                "organization_type" => [
                    "field_type"    => "dropdown",
                    "list_type"     => "dialog",
                    "name"          => "organization_type",
                    "id"            => "organization_type",
                    "value"         => null,
                    "title"         => "প্রতিষ্ঠানের ধরণ",
                    "hint"          => "এখানে লিখুন",
                    "error_message" => "",
                    "mandatory"     => false,
                    "is_editable"   => true,
                    "list"          => [
                        [
                            "key" => "good",
                            "en"  => "Good",
                            "bn"  => "ভালো"
                        ],
                        [
                            "key" => "bad",
                            "en"  => "Bad",
                            "bn"  => "খারাপ"
                        ],
                    ]
                ],
                "business_type" => [
                    "field_type"    => "dropdown",
                    "list_type"     => "dialog",
                    "name"          => "business_type",
                    "id"            => "business_type",
                    "value"         => null,
                    "title"         => "ব্যবসার ধরণ",
                    "hint"          => "এখানে লিখুন",
                    "error_message" => "",
                    "mandatory"     => false,
                    "is_editable"   => true,
                    "list"          => constants('PARTNER_BUSINESS_TYPES')
                ],
                "yearly_earning" => [
                    "field_type"    => "editText",
                    "input_type"    => "text",
                    "name"          => "yearly_earning",
                    "id"            => "yearly_earning",
                    "value"         => null,
                    "title"         => "বাৎসরিক আয়ের পরিমান",
                    "hint"          => "উদাহরণ: 10000",
                    "error_message" => "",
                    "mandatory"     => false,
                    "is_editable"   => true
                ],

            ],
            "completion_percentage" => "15%"
        ];
    }

    public function accountDetails()
    {
        return [
          'account_info' => [
              'account_name' => 'AL Amin Rahman',
              'account_no' => '2441139',
              'balance' => '4000',
              'minimum_transaction_amount' => 1000,
              'transaction_error_msg' => 'ট্রান্সেকশন সফল হয়েছে'
          ],
          'transactions' => [
              [
                  'date' => '2020-12-01 20:10:33',
                  'name' => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
                  'mobile' => '01748712884',
                  'amount' => '60000',
                  'type'  => 'credit'
              ],
              [
                  'date' => '2020-12-01 20:10:33',
                  'name' => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
                  'mobile' => '01748712884',
                  'amount' => '30000',
                  'type'  => 'debit'
              ],
              [
                  'date' => '2020-12-01 20:10:33',
                  'name' => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
                  'mobile' => '01748712884',
                  'amount' => '60000',
                  'type'  => 'debit'
              ],
              [
                  'date' => '2020-12-01 20:10:33',
                  'name' => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
                  'mobile' => '01748712884',
                  'amount' => '20000',
                  'type'  => 'credit'
              ],
              [
                  'date' => '2020-12-01 20:10:33',
                  'name' => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
                  'mobile' => '01748712884',
                  'amount' => '10000',
                  'type'  => 'credit'
              ],
          ]
        ];

    }

    public function createTransaction()
    {
        return [
            'status' => 'success',
            'heading' => 'ট্রান্সেকশন সফল হয়েছে',
            'message' => 'ট্রান্সেকশন সফল হয়েছে'
        ];

    }


    public function getCompletion()
    {
        $data['completion'] = [
            [
                'title' => [
                    'en' => 'NID and Selfie',
                    'bn' => 'জাতীয় পরিচয়পত্র ও সেলফি'
                ],
                'completion_percentage' => [
                    'en' => 75,
                    'bn' => '৭৫'
                ],
                'last_updated' => 'today'
            ],
            [
                'title' => [
                    'en' => 'Institution',
                    'bn' => 'প্রতিষ্ঠান সম্পর্কিত তথ্য'
                ],
                'completion_percentage' => [
                    'en' => 75,
                    'bn' => '৭৫'
                ],
                'last_updated' => 'yesterday'
            ],
            [
                'title' => [
                    'en' => 'Personal',
                    'bn' => 'ব্যক্তিগত তথ্য '
                ],
                'completion_percentage' => [
                    'en' => 75,
                    'bn' => '৭৫'
                ],
                'last_updated' => 'yesterday'
            ],
            [
                'title' => [
                    'en' => 'Nominee',
                    'bn' => 'বনমিনি তথ্য '
                ],
                'completion_percentage' => [
                    'en' => 75,
                    'bn' => '৭৫'
                ],
                'last_updated' => 4
            ],
            [
                'title' => [
                    'en' => 'Documents',
                    'bn' => 'প্রয়ােজনীয় ডকুমেন্ট আপলোড '
                ],
                'completion_percentage' => [
                    'en' => 75,
                    'bn' => '৭৫'
                ],
                'last_updated' => 4
            ],
            [
                'title' => [
                    'en' => 'Account',
                    'bn' => 'অ্যাকাউন্ট সম্পর্কিত তথ্য '
                ],
                'completion_percentage' => [
                    'en' => 75,
                    'bn' => '৭৫'
                ],
                'last_updated' => 4
            ]
        ];
        $data['can_apply_account'] = 1;
        $data['bank_details_link'] = env('SHEBA_PARTNER_END_URL') . '/' .'neo-banking-account-details';
        $data['message'] = 'প্রয়োজনীয় তথ্য দেয়া সম্পন্ন হয়েছ, আপনি ব্যাংক অ্যাকাউন্ট জন্য আবেদন করতে পারবেন।';
        $data['message_type'] = 'info';

        return $data;

    }

}