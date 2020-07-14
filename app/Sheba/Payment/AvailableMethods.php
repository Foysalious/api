<?php namespace Sheba\Payment;

use App\Models\Payable;
use Exception;
use Sheba\Dal\Payable\Types;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;

class AvailableMethods
{
    /**
     * @param $payable_type
     * @param $payable_type_id
     * @param $version_code
     * @param $platform_name
     * @param $user_type
     * @return array
     * @throws Exceptions\InvalidPaymentMethod
     */
    public static function getDetails($payable_type, $payable_type_id, $version_code, $platform_name, $user_type)
    {
        $payable_type = $payable_type ?: "order";

        $payable = new Payable();

        switch ($payable_type) {
            case 'order':
                $methods = self::getRegularPayments();
                $payable->type = Types::PARTNER_ORDER;
                break;
            case 'subscription':
                $methods = self::getSubscriptionPayments();
                $payable->type = Types::SUBSCRIPTION_ORDER;
                break;
            case 'voucher':
                $methods = self::getVoucherPayments();
                $payable->type = Types::GIFT_CARD_PURCHASE;
                break;
            case 'movie_ticket':
                $payable->type = Types::MOVIE_TICKET_PURCHASE;
            case 'transport_ticket':
                $methods = self::getTicketsPayments($user_type);
                $payable->type = Types::TRANSPORT_TICKET_PURCHASE;
                break;
            case 'business':
                $methods = self::getBusinessPayments();
                $payable->type = Types::PROCUREMENT;
                break;
            case 'utility':
                $methods = self::getUtilityPayments();
                $payable->type = Types::UTILITY_ORDER;
                break;
            case 'payment_link':
                $methods = self::getPaymentLinkPayments($payable_type_id);
                $payable->type = Types::PAYMENT_LINK;
                break;
            case 'wallet_recharge':
                $methods = self::getWalletRechargePayments();
                $payable->type = Types::WALLET_RECHARGE;
                break;
            default:
                throw new Exception('Invalid Payable Type');
        }

        $details = [];
        foreach ($methods as $method) {
            $details[] = PaymentStrategy::getDetails($method, $version_code, $platform_name, $payable);
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
}
