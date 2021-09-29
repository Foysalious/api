<?php

namespace Sheba\ComplianceInfo;

class Statics
{
    public static function complianceInfoUpdateValidation(): array
    {
        return [
            "shop_type"         => 'in:virtual,both,physical',
            "tin_licence_image" => 'file|mimes:jpeg,png,jpg',
            "electricity_bill_image" => 'file|mimes:jpeg,png,jpg'
        ];
    }

    public static function complianceInfoUpdateFields(): array
    {
        return [
            'shop_type', 'monthly_transaction_volume', 'registration_year', 'email', 'trade_license',
            'tin_licence_photo', 'electricity_bill_image'
        ];
    }
}
