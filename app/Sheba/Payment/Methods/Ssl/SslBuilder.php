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
}
