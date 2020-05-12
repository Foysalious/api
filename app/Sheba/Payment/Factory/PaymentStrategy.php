<?php namespace Sheba\Payment\Factory;

use InvalidArgumentException;
use Sheba\Helpers\ConstGetter;

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
        return self::PORT_WALLET;
    }

    public static function getDetails($method, $version_code, $platform_name)
    {
        if (!self::isValid($method)) throw new InvalidArgumentException('Invalid Method.');

        if ($method == self::ONLINE) $method = self::getDefaultOnlineMethod();

        switch ($method) {
            case self::SSL: return self::sslDetails();
            case self::BKASH: return self::bkashDetails();
            case self::WALLET: return self::shebaCreditDetails();
            case self::CBL: return self::cblDetails($version_code, $platform_name);
            case self::PARTNER_WALLET: return self::partnerWalletDetails();
            case self::OK_WALLET: return self::okWalletDetails();
            case self::SSL_DONATION: return self::sslDetails();
            case self::PORT_WALLET: return self::portWalletDetails();
        }
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
