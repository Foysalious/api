<?php

namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use Carbon\Carbon;

class SubscriptionStatics
{
    /**
     * @param Partner $partner
     * @param $price
     * @return string
     */
    public static function getPackageMessage(Partner $partner, $price)
    {
        $date = Carbon::parse($partner->next_billing_date);
        $month = banglaMonth($date->month);
        $date  = convertNumbersToBangla($date->day, false);
        return "আপনি বর্তমানে বেসিক প্যাকেজ ব্যবহার করছেন। স্বয়ংক্রিয় নবায়ন এর জন্য $date $month $price টাকা বালান্স রাখুন।";
    }

    public static function getLitePackageID()
    {
        return config('sheba.partner_lite_packages_id');
    }

    public static function getLitePackageMessage()
    {
        return config('sheba.lite_package_message');
    }

    public static function getPackageStaticDiscount()
    {
        return [
            'monthly_tag'                => null, 'half_yearly_tag' => '১৯% ছাড়', 'yearly_tag' => '৫০% ছাড়',
            'tags'                       => [
                'monthly'     => ['en' => null, 'bn' => null],
                'half_yearly' => ['en' => '19% discount', 'bn' => '১৯% ছাড়'],
                'yearly'      => ['en' => '50% discount', 'bn' => '৫০% ছাড়']
            ]
        ];
    }

    public static function getPartnerSubscriptionVat()
    {
        return config('sheba.partner_subscription_vat');
    }
}