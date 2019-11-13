<?php namespace App\Sheba\BankingInfo;

use Sheba\Helpers\ConstGetter;

class MobileBanking
{
    use ConstGetter;

    const BKASH = 'bKash';
    const EASYCASH= 'EasyCash';
    const MCASH = 'mCash';
    const SURECASH = 'SureCash';
    const ROCKET = 'Rocket';
    const MYCASH = 'MyCash';

    public static function getPublishedBank()
    {
        return [ self::BKASH, self::SURECASH, self::ROCKET ];
    }
}
