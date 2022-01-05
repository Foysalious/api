<?php

namespace Sheba\MerchantEnrollment\Statics;

class MEFGeneralStatics
{
    public static function payment_gateway_keys()
    {
        return config('reseller_payment.available_payment_gateway_keys');
    }

    public static function get_category_validation(): array
    {
        return [
            "key" => 'required|in:'.implode(',', self::payment_gateway_keys()),
            "category_code" => 'required|string'
        ];
    }
}