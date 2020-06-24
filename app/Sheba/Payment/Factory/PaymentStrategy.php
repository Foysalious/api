<?php namespace Sheba\Payment\Factory;

use App\Models\Customer;
use App\Models\Partner;
use Sheba\Helpers\ConstGetter;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\Methods\Bkash\Bkash;
use Sheba\Payment\Methods\Cbl\Cbl;
use Sheba\Payment\Methods\OkWallet\OkWallet;
use Sheba\Payment\Methods\PartnerWallet;
use Sheba\Payment\Methods\PortWallet\PortWallet;
use Sheba\Payment\Methods\Ssl\Ssl;
use Sheba\Payment\Methods\Ssl\SslBuilder;
use Sheba\Payment\Methods\Wallet;
use Sheba\Payment\PayableUser;

class PaymentStrategy
{
    use ConstGetter;

    const BKASH = "bkash";
    const ONLINE = "online";
    const SSL = "ssl";
    const WALLET = "wallet";
    const CBL = "cbl";
    const PARTNER_WALLET = "partner_wallet";
    const OK_WALLET = 'ok_wallet';
    const SSL_DONATION = "ssl_donation";
    const PORT_WALLET = "port_wallet";

    public static function getDefaultOnlineMethod()
    {
        return self::SSL;
    }

    /**
     * @param $method
     * @param PayableUser $user
     * @return Bkash|Cbl|Ssl|Wallet|PartnerWallet|OkWallet|PortWallet
     * @throws InvalidPaymentMethod
     */
    public static function getMethod($method, PayableUser $user)
    {
        switch (self::getValidatedMethod($method, $user)) {
            case self::SSL: return SslBuilder::get($user);
            case self::SSL_DONATION: return SslBuilder::getForDonation();
            case self::BKASH: return app(Bkash::class);
            case self::WALLET: return app(Wallet::class);
            case self::CBL: return app(Cbl::class);
            case self::PARTNER_WALLET: return app(PartnerWallet::class);
            case self::OK_WALLET: return app(OkWallet::class);
            case self::PORT_WALLET: return app(PortWallet::class);
        }
    }

    /**
     * @param $method
     * @param $version_code
     * @param $platform_name
     * @param PayableUser $user
     * @return array
     * @throws InvalidPaymentMethod
     */
    public static function getDetails($method, $version_code, $platform_name, PayableUser $user)
    {
        switch (self::getValidatedMethod($method, $user)) {
            case self::SSL:
            case self::SSL_DONATION: return self::sslDetails();
            case self::BKASH: return self::bkashDetails();
            case self::WALLET: return self::shebaCreditDetails();
            case self::CBL: return self::cblDetails($version_code, $platform_name);
            case self::PARTNER_WALLET: return self::partnerWalletDetails();
            case self::OK_WALLET: return self::okWalletDetails();
            case self::PORT_WALLET: return self::portWalletDetails();
        }
    }

    /**
     * @param $method
     * @param PayableUser $user
     * @return string
     * @throws InvalidPaymentMethod
     */
    private static function getValidatedMethod($method, PayableUser $user)
    {
        if (!self::isValid($method)) throw new InvalidPaymentMethod();

        if ($method == self::ONLINE) {
            if ($user instanceof Customer) $method = self::SSL;
            else if ($user instanceof Partner) $method = self::PORT_WALLET;
            else $method = self::getDefaultOnlineMethod();
        }

        return $method;
    }

    /**
     * @return array
     */
    private static function shebaCreditDetails()
    {
        return [
            'name' => 'Sheba Credit',
            'is_published' => 1,
            'description' => '',
            'asset' => 'sheba_credit',
            'method_name' => 'wallet'
        ];
    }

    /**
     * @return array
     */
    private static function partnerWalletDetails()
    {
        return [
            'name' => 'Sheba Credit',
            'is_published' => 1,
            'description' => '',
            'asset' => 'sheba_credit',
            'method_name' => 'wallet'
        ];
    }

    /**
     * @return array
     */
    private static function bkashDetails()
    {
        return [
            'name' => 'bKash',
            'is_published' => 1,
            'description' => '',
            'asset' => 'bkash',
            'method_name' => 'bkash'
        ];
    }

    /**
     * @param $version_code
     * @param $platform_name
     * @return array
     */
    private static function cblDetails($version_code, $platform_name)
    {
        return [
            'name' => 'City Bank',
            'is_published' => self::getCblStatus($version_code, $platform_name),
            'description' => '',
            'asset' => 'cbl',
            'method_name' => 'cbl'
        ];
    }

    /**
     * @param $version_code
     * @param $platform_name
     * @return int
     */
    private static function getCblStatus($version_code, $platform_name)
    {
        if (!$version_code) return 1;

        return $platform_name && $platform_name == 'ios' ? 1 : ($version_code > 30112 ? 1 : 0);
    }

    /**
     * @return array
     */
    private static function sslDetails()
    {
        return [
            'name' => 'Other Debit/Credit',
            'is_published' => 1,
            'description' => '',
            'asset' => 'ssl',
            'method_name' => 'online'
        ];
    }


    /**
     * @return array
     */
    private static function portWalletDetails()
    {
        return [
            'name' => 'Other Debit/Credit',
            'is_published' => 1,
            'description' => '',
            'asset' => 'ssl',
            'method_name' => 'online'
        ];
    }

    private static function okWalletDetails()
    {
        return [
            'name' => 'Ok Wallet',
            'is_published' => 1,
            'description' => '',
            'asset' => 'ok_wallet',
            'method_name' => 'ok_wallet'
        ];
    }
}
