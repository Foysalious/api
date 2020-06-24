<?php namespace Sheba\Payment;

use Exception;
use Sheba\Payment\Factory\PaymentStrategy;

class AvailableMethods
{
    /**
     * @param $payable_type
     * @param $version_code
     * @param $platform_name
     * @param $user_type
     * @return array
     * @throws Exception
     */
    public static function getDetails($payable_type, $version_code, $platform_name, $user_type)
    {
        $payable_type = $payable_type ?: "order";

        switch ($payable_type) {
            case 'order':
                $methods = self::getRegularPayments();
                break;
            case 'subscription':
                $methods = self::getSubscriptionPayments();
                break;
            case 'voucher':
                $methods = self::getVoucherPayments();
                break;
            case 'movie_ticket':
            case 'transport_ticket':
                $methods = self::getTicketsPayments($user_type);
                break;
            case 'business':
                $methods = self::getBusinessPayments();
                break;
            case 'utility':
                $methods = self::getUtilityPayments();
                break;
            case 'payment_link':
                $methods = self::getPaymentLinkPayments();
                break;
            case 'wallet_recharge':
                $methods = self::getWalletRechargePayments();
                break;
            default:
                throw new Exception('Invalid Payable Type');
        }

        $model = "\\App\\Models\\" . studly_case($user_type);
        $empty_user = new $model();

        $details = [];
        foreach ($methods as $method) {
            $details[] = PaymentStrategy::getDetails($method, $version_code, $platform_name, $empty_user);
        }

        return $details;
    }

    public static function getRegularPayments()
    {
        return [
            PaymentStrategy::WALLET,
            PaymentStrategy::BKASH,
            PaymentStrategy::CBL,
            PaymentStrategy::ONLINE,
        ];
    }

    public static function getSubscriptionPayments()
    {
        return [
            PaymentStrategy::WALLET,
            PaymentStrategy::BKASH,
            PaymentStrategy::CBL,
            PaymentStrategy::ONLINE,
        ];
    }

    public static function getVoucherPayments()
    {
        return [
            PaymentStrategy::CBL,
            PaymentStrategy::ONLINE,
        ];
    }

    public static function getWalletRechargePayments()
    {
        return [
            PaymentStrategy::CBL,
            PaymentStrategy::ONLINE,
            PaymentStrategy::BKASH,
            PaymentStrategy::OK_WALLET
        ];
    }

    public static function getTicketsPayments($user_type)
    {
        if (isset($user_type) && $user_type !== 'customer') {
            return [
                PaymentStrategy::WALLET
            ];
        }

        return [
            PaymentStrategy::CBL,
            PaymentStrategy::ONLINE,
            PaymentStrategy::BKASH,
            PaymentStrategy::WALLET
        ];
    }

    public static function getBusinessPayments()
    {
        return [
            PaymentStrategy::CBL,
            PaymentStrategy::ONLINE,
            PaymentStrategy::BKASH
        ];
    }

    public static function getUtilityPayments()
    {
        return [
            PaymentStrategy::CBL,
            PaymentStrategy::ONLINE,
            PaymentStrategy::BKASH,
            PaymentStrategy::WALLET
        ];
    }

    public static function getPaymentLinkPayments()
    {
        return [
            PaymentStrategy::CBL,
            PaymentStrategy::BKASH,
            PaymentStrategy::SSL_DONATION
        ];
    }
}
