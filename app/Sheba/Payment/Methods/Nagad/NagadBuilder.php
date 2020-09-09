<?php


namespace App\Sheba\Payment\Methods\Nagad;

use App\Models\Affiliate;
use App\Models\Payable;
use Sheba\Payment\Methods\Nagad\Stores\AffiliateStore;
use Sheba\Payment\Methods\Nagad\Stores\DefaultStore;
use Sheba\Payment\Methods\Nagad\Nagad;
use Sheba\Payment\Methods\Ssl\Ssl;
use Sheba\Payment\PayableUser;

class NagadBuilder
{

    public static function get(Payable $payable)
    {
        /** @var Nagad $nagad */
        $nagad = app(Nagad::class);
        $nagad->setStore(self::getStore($payable));
        return $nagad;
    }

    public static function getStore(Payable $payable)
    {
        /** @var PayableUser $user */
        $user = $payable->user;

        if ($user instanceof Affiliate) return new AffiliateStore();

        return new DefaultStore();
    }

    /**
     * @param $store_name
     * @return Ssl
     */
    public static function getByStoreName($store_name)
    {
        /** @var Nagad $ssl */
        $nagad = app(Nagad::class);
        $nagad->setStore(self::getStoreByName($store_name));
        return $nagad;
    }

    public static function getStoreByName($name)
    {
        if ($name == AffiliateStore::NAME) return new AffiliateStore();
        return new DefaultStore();
    }



    /**
     * @param Payable $payable
     * @return bool
     */
    public static function isPortWalletFailed(Payable $payable)
    {
        return $payable->payments()->initiationFailed()->count() > 0;
    }

}