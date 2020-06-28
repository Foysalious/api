<?php namespace Sheba\Payment\Methods\Ssl;

use App\Models\Customer;
use Sheba\Payment\Methods\Ssl\Stores\DefaultStore;
use Sheba\Payment\Methods\Ssl\Stores\Donation;
use Sheba\Payment\Methods\Ssl\Stores\MarketPlace;
use Sheba\Payment\Methods\Ssl\Stores\SslStore;
use Sheba\Payment\PayableUser;

class SslBuilder
{
    /**
     * @param PayableUser $user
     * @return Ssl
     */
    public static function get(PayableUser $user)
    {
        /** @var Ssl $ssl */
        $ssl = app(Ssl::class);
        $ssl->setStore(self::getStore($user));
        return $ssl;
    }

    /**
     * @return Ssl
     */
    public static function getForDonation()
    {
        /** @var Ssl $ssl */
        $ssl = app(Ssl::class);
        $ssl->setStore(new Donation())->forDonation();
        return $ssl;
    }

    /**
     * @param PayableUser $user
     * @return SslStore
     */
    public static function getStore(PayableUser $user)
    {
        if ($user instanceof Customer) return new MarketPlace();

        return new DefaultStore();
    }

    /**
     * @param $store_name
     * @return Ssl
     */
    public static function getByStoreName($store_name)
    {
        /** @var Ssl $ssl */
        $ssl = app(Ssl::class);
        $ssl->setStore(self::getStoreByName($store_name));
        return $ssl;
    }

    public static function getStoreByName($name)
    {
        if ($name == MarketPlace::NAME) return new MarketPlace();

        if ($name == Donation::NAME) return new Donation();

        return new DefaultStore();
    }
}
