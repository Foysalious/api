<?php namespace App\Sheba\Partner\KYC;

use Sheba\Helpers\ConstGetter;


class RestrictedFeature
{
    public static function get()
    {
        return [
            'wallet_recharge',
            'digital_collection',
            'product_link',
            'digital_collection_from_pos',
            'emi_collection',
            'emi_use',
            'withdrawal',
            'package_update'
        ];
    }

}