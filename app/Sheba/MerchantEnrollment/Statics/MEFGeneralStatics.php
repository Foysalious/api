<?php

namespace Sheba\MerchantEnrollment\Statics;

class MEFGeneralStatics
{
    const USER_TYPE_PARTNER = "Partner";

    const LIST_PAGE_BANNER = "https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/partner/reseller_payment/list_banner.png";

    public static function payment_gateway_keys()
    {
        return config('reseller_payment.available_payment_gateway_keys');
    }

    public static function payment_gateway_key_validation(): array
    {
        return [
            "key" => 'required|in:'.implode(',', self::payment_gateway_keys()),
        ];
    }

    public static function get_category_validation(): array
    {
        return array_merge(self::payment_gateway_key_validation(), [
            "category_code" => 'required|string'
        ]);
    }

    public static function category_store_validation(): array
    {
        return array_merge(self::get_category_validation(), [
            "data" => "required"
        ]);
    }

    public static function document_upload_validation(): array
    {
        return array_merge(self::get_category_validation(), [
            'document'    => 'required|file',
            'document_id' => 'required'
        ]);
    }

    public static function required_documents(): array
    {
        return config('reseller_payment.required_documents');
    }

    public static function types($type): array
    {
        $data = [
            'organization_type_list' => ['list' => config('occupation_nature.organization_type'), 'title' => 'প্রতিষ্ঠানের ধরণ সিলেক্ট করুন'],
            'company_type'=> ['list' => config('occupation_nature.data'),'title'=>'ব্যবসার ধরণ সিলেক্ট করুন']
        ];

        return  $data[$type];
    }
}