<?php namespace Sheba\NeoBanking;

use App\Sheba\NeoBanking\Banks\BankAccountInfoWithTransaction;
use Sheba\Dal\NeoBank\Model as NeoBank;
use Sheba\NeoBanking\Banks\BankFactory;
use Sheba\NeoBanking\Banks\BankFormCategoryFactory;
use Sheba\NeoBanking\DTO\BankFormCategory;
use Sheba\NeoBanking\Repositories\NeoBankRepository;

class NeoBanking
{
    /** @var NeoBank $bank */
    private $bank;
    private $partner;
    private $resource;
    private $post_data;

    public function __construct()
    {
    }


    /**
     * @param mixed $post_data
     * @return NeoBanking
     */
    public function setPostData($post_data)
    {
        $this->post_data = (array)json_decode($post_data, 0);
        return $this;
    }

    public function setBank($bank)
    {
        if (!($bank instanceof NeoBank)) $bank = (new NeoBankRepository())->getByCode($bank);
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
                [
                    "field_type" => "header",
                    "title"      => "যোগাযোগ এর তথ্য",
                ],
                [
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
                [
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
                [
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
                [
                    "field_type" => "header",
                    "title"      => "যোগাযোগ এর তথ্য",
                ],
                [
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
                [
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
                [
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
                [
                    "field_type" => "header",
                    "title"      => "রেজিস্ট্রেশন সম্পর্কিত তথ্য",
                ],
                [
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
                [
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
                [
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
                [
                    "field_type" => "header",
                    "title"      => "ব্যাবসা / অফিস - এর ঠিকানা",
                ],
                [
                    "field_type" => "doubleView",
                    "name"       => "street_or_village_and_postcode",
                    "id"         => "street_or_village_and_postcode",
                    "views"      => [
                        [
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
                        [
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
                [
                    "field_type" => "doubleView",
                    "name"       => "street_or_village_and_postcode",
                    "id"         => "street_or_village_and_postcode",
                    "views"      => [
                        [
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
                                    "key" => "gaibandha",
                                    "en"  => "Gaibandha",
                                    "bn"  => "গাইবান্ধা"
                                ],
                                [
                                    "key" => "dhaka",
                                    "en"  => "Dhaka",
                                    "bn"  => "ঢাকা"
                                ]
                            ]
                        ],
                        [
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
                                    "key" => "gaibandha",
                                    "en"  => "Gaibandha",
                                    "bn"  => "গাইবান্ধা"
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
                [
                    "field_type" => "doubleView",
                    "name"       => "business_country",
                    "id"         => "business_country",
                    "views"      => [
                        [
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
                [
                    "field_type" => "header",
                    "title"      => "অন্যান্য তথ্য",
                ],
                [
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
                [
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
                [
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
                [
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
                [
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
            "completion_percentage"    => "15%"
        ];
    }

    /**
     * @return BankAccountInfoWithTransaction
     * @throws Exceptions\InvalidBankCode
     */
    public function accountDetails(): BankAccountInfoWithTransaction
    {
//        return [
//            'account_info' => [
//                'account_name'               => 'AL Amin Rahman',
//                'account_no'                 => '2441139',
//                'balance'                    => '4000',
//                'minimum_transaction_amount' => 1000,
//                'transaction_error_msg'      => 'ট্রান্সেকশন সফল হয়েছে'
//            ],
//            'transactions' => [
//                [
//                    'date'   => '2020-12-01 20:10:33',
//                    'name'   => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
//                    'mobile' => '01748712884',
//                    'amount' => '60000',
//                    'type'   => 'credit'
//                ],
//                [
//                    'date'   => '2020-12-01 20:10:33',
//                    'name'   => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
//                    'mobile' => '01748712884',
//                    'amount' => '30000',
//                    'type'   => 'debit'
//                ],
//                [
//                    'date'   => '2020-12-01 20:10:33',
//                    'name'   => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
//                    'mobile' => '01748712884',
//                    'amount' => '60000',
//                    'type'   => 'debit'
//                ],
//                [
//                    'date'   => '2020-12-01 20:10:33',
//                    'name'   => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
//                    'mobile' => '01748712884',
//                    'amount' => '20000',
//                    'type'   => 'credit'
//                ],
//                [
//                    'date'   => '2020-12-01 20:10:33',
//                    'name'   => 'Ikhtiar uddin Mohammad Bakhtiar Khilji',
//                    'mobile' => '01748712884',
//                    'amount' => '10000',
//                    'type'   => 'credit'
//                ],
//            ]
//        ];
        return (new BankFactory())->setPartner($this->partner)->setBank($this->bank)->get()->accountDetailInfo();
    }

    public function createTransaction()
    {
        return [
            'status'  => 'success',
            'heading' => 'ট্রান্সেকশন সফল হয়েছে',
            'message' => 'ট্রান্সেকশন সফল হয়েছে'
        ];

    }

    /**
     * @return mixed
     * @throws Exceptions\InvalidBankCode
     */
    public function homepage()
    {
        return (new Home())->setPartner($this->partner)->get();
    }

    /**
     * @return Banks\BankCompletion
     * @throws Exceptions\InvalidBankCode
     */
    public function getCompletion()
    {
        return (new BankFactory())->setPartner($this->partner)->setBank($this->bank)->get()->completion();

    }

    /**
     * @param $category_code
     * @return array
     * @throws Exceptions\InvalidBankCode
     * @throws Exceptions\InvalidBankFormCategoryCode
     */
    public function getCategoryDetail($category_code)
    {
        $bank = (new BankFactory())->setPartner($this->partner)->setBank($this->bank)->get();
        return $bank->categoryDetails((new BankFormCategoryFactory())->setBank($bank)->getCategoryByCode($category_code))->toArray();

    }

    public function getNidInfo($data)
    {
        $bank = (new BankFactory())->setPartner($this->partner)->setBank($this->bank)->get();
        return $bank->getNidInfo($data);
    }

    /**
     * @param $category_code
     * @throws Exceptions\InvalidBankCode
     * @throws Exceptions\InvalidBankFormCategoryCode
     * @throws Exceptions\CategoryPostDataInvalidException
     */
    public function postCategoryDetail($category_code)
    {
        $bank     = (new BankFactory())->setPartner($this->partner)->setBank($this->bank)->get();
        $category = (new BankFormCategoryFactory())->setBank($bank)->setPartner($this->partner)->getCategoryByCode($category_code);
        return $bank->loadInfo()->validateCategoryDetail($category, $this->post_data)->postCategoryDetail($category, $this->post_data);
    }

}
