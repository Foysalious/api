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

    public function businessInformation()
    {
        return [
            "business_information" => [
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
                "street_or_village" => [
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

            ],
            "completion_percentage" => "15%"
        ];
    }

}