<?php namespace Sheba\Payment;

use App\Models\Payable;
use Exception;
use Sheba\Dal\Payable\Types;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\Presenter\PaymentMethodDetails;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;

class AvailableMethods
{
    /**
     * @param $payable_type
     * @param $payable_type_id
     * @param $version_code
     * @param $platform_name
     * @param $user_type
     * @return PaymentMethodDetails[]
     * @throws Exception
     */
    public static function getDetails($payable_type, $payable_type_id, $version_code, $platform_name, $user_type)
    {
        $methods = self::getMethods($payable_type, $payable_type_id, $user_type);

        $details = [];
        foreach ($methods as $method) {
            $detail = new PaymentMethodDetails($method);
            if ($method == PaymentStrategy::CBL) {
                $detail->setIsPublished(self::getCblStatus($version_code, $platform_name));
            }
            $details[] =  $detail;
        }

        return $details;
    }

    /**
     * @param $payable_type
     * @param $payable_type_id
     * @param $user_type
     * @return array
     * @throws Exception
     */
    public static function getMethods($payable_type, $payable_type_id, $user_type)
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
                $methods = self::getPaymentLinkPayments($payable_type_id);
                break;
            case 'wallet_recharge':
                $methods = self::getWalletRechargePayments();
                break;
            default:
                throw new Exception('Invalid Payable Type');
        }

        return $methods;
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

    public static function getPaymentLinkPayments($payment_link_identifier)
    {
        /*
         * TODO: Load payment methods depending on the link.
         *
         * /** @var PaymentLinkRepositoryInterface $repo *
         * $repo = app(PaymentLinkRepositoryInterface::class);
         * $payment_link = $repo->findByIdentifier($payment_link_identifier);
         * if ($payment_link->isForMissionSaveBangladesh()) return [PaymentStrategy::ONLINE];
         * if ($payment_link->isEmi()) return [PaymentStrategy::ONLINE];
         *
         */

        return [
            PaymentStrategy::CBL,
            PaymentStrategy::BKASH,
            PaymentStrategy::ONLINE,
            PaymentStrategy::SSL_DONATION
        ];
    }

    /**
     * @param $version_code
     * @param $platform_name
     * @return bool
     */
    private static function getCblStatus($version_code, $platform_name)
    {
        if (!$version_code) return true;

        return $platform_name && $platform_name == 'ios' ? true : ($version_code > 30112);
    }

}
