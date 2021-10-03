<?php

namespace Sheba\ComplianceInfo;

use Carbon\Carbon;

class Statics
{
    const VERIFIED = "verified";
    const REJECTED = "rejected";


    public static function complianceInfoUpdateValidation(): array
    {
        return [
            "shop_type"         => 'in:virtual,both,physical',
            "tin_licence_image" => 'file|mimes:jpeg,png,jpg',
            "electricity_bill_image" => 'file|mimes:jpeg,png,jpg',
            'registration_year' => 'date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d')
        ];
    }

    public static function complianceInfoUpdateFields(): array
    {
        return [
            'shop_type', 'monthly_transaction_volume', 'registration_year', 'email', 'trade_license',
            'tin_licence_photo', 'electricity_bill_image', 'tin_no'
        ];
    }

    /**
     * @return string[]
     */
    public static function complianceRejectedMessage(): array
    {
        return [
            "title" => "তথ্য দিয়ে সহায়তা করুন।",
            "message" => "চলতি মাসে আপনার ১ লক্ষ টাকার বেশি লেনদেন হয়েছে। কিছু এককালীন তথ্য দিয়ে আমদের সহ্যটা করুন।"
        ];
    }
}
