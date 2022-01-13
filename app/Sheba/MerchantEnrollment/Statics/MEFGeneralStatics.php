<?php

namespace Sheba\MerchantEnrollment\Statics;

class MEFGeneralStatics
{
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
}