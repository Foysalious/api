<?php namespace Sheba\Subscription\Partner;

use Sheba\Helpers\ConstGetter;

class BillingType
{
    use ConstGetter;

    const MONTHLY = 'monthly';
    const HALF_YEARLY = 'half_yearly';
    const YEARLY = 'yearly';

    public static function BN()
    {
        return [self::YEARLY => 'বাৎসরিক', self::MONTHLY => "মাসিক", self::HALF_YEARLY => "অর্ধ বার্ষিক"];
    }
}
