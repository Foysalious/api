<?php namespace Sheba\Payment\Methods\Ssl;

use App\Models\Customer;
use App\Models\Payable;
use App\Sheba\PaymentLink\PaymentLinkOrder;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\Methods\Ssl\Stores\DefaultStore;
use Sheba\Payment\Methods\Ssl\Stores\Donation;
use Sheba\Payment\Methods\Ssl\Stores\MarketPlace;
use Sheba\Payment\Methods\Ssl\Stores\SslStore;
use Sheba\Payment\PayableUser;
use Sheba\PaymentLink\PaymentLinkTransformer;

class SslBuilder
{
    /**
     * @param Payable $payable
     * @return Ssl
     * @throws InvalidPaymentMethod
     */
    public static function get(Payable $payable)
    {
        /** @var Ssl $ssl */
        $ssl = app(Ssl::class);
        $ssl->setStore(self::getStore($payable));
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
     * @param Payable $payable
     * @return SslStore
     * @throws InvalidPaymentMethod
     */
    public static function getStore(Payable $payable)
    {
        /** @var PayableUser $user */
        $user = $payable->user;

        if ($payable->isPaymentLink()) {
            return self::getStoreForPaymentLink($payable->getPaymentLink());
        }

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

    /**
     * @param PaymentLinkTransformer $payment_link
     * @return bool
     */
    public static function shouldUseForPaymentLink(PaymentLinkTransformer $payment_link)
    {
        try {
            self::getStoreForPaymentLink($payment_link);
            return true;
        } catch (InvalidPaymentMethod $e) {
            return false;
        }
    }

    /**
     * @param PaymentLinkTransformer $payment_link
     * @return SslStore
     * @throws InvalidPaymentMethod
     */
    public static function getStoreForPaymentLink(PaymentLinkTransformer $payment_link)
    {
        if ($payment_link->isForMissionSaveBangladesh()) return new Donation();

        if ($payment_link->getEmiMonth()) return new DefaultStore();

        throw new InvalidPaymentMethod("SSL is not used for general payment links.");
    }
}
