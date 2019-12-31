<?php namespace Sheba\Payment;


use Exception;

class AvailableMethods
{
    /**
     * @param $payable_type
     * @param $version_code
     * @param $platform_name
     * @return array
     * @throws Exception
     */
    public static function get($payable_type, $version_code, $platform_name)
    {
        if ($payable_type) {
            switch ($payable_type) {
                case 'order':
                    $payments = self::getRegularPayments($version_code, $platform_name);
                    break;
                case 'subscription':
                    $payments = self::getSubscriptionPayments($version_code, $platform_name);
                    break;
                case 'voucher':
                    $payments = self::getVoucherPayments($version_code, $platform_name);
                    break;
                case 'movie_ticket':
                case 'transport_ticket':
                    $payments = self::getTicketsPayments($version_code, $platform_name);
                    break;
                case 'business':
                    $payments = self::getBusinessPayments($version_code, $platform_name);
                    break;
                case 'utility':
                    $payments = self::getUtilityPayments($version_code, $platform_name);
                    break;
                case 'payment_link':
                    $payments = self::getPaymentLinkPayments($version_code, $platform_name);
                    break;
                case 'wallet_recharge':
                    $payments = self::getWalletRechargePayments($version_code, $platform_name);
                    break;
                default:
                    throw new Exception('Invalid Payable Type');
                    break;
            }
        } else {
            $payments = self::getRegularPayments($version_code, $platform_name);
        }
        return $payments;
    }

    private static function getVoucherPayments($version_code, $platform_name)
    {
        return [
            self::cbl($version_code, $platform_name),
            self::ssl()
        ];
    }

    private static function getRegularPayments($version_code, $platform_name)
    {
        return [
            self::shebaCredit(),
            self::bkash(),
            self::cbl($version_code, $platform_name),
            self::ssl()
        ];
    }

    private static function getBusinessPayments($version_code, $platform_name)
    {
        return [
            self::bkash(),
            self::cbl($version_code, $platform_name),
            self::ssl()
        ];
    }

    private static function getSubscriptionPayments($version_code, $platform_name)
    {
        return [
            self::shebaCredit(),
            self::cbl($version_code, $platform_name),
            self::bkash(),
            self::ssl()
        ];
    }


    private static function getTicketsPayments($version_code, $platform_name)
    {
        if (isset(\request()->type) && \request()->type === 'customer') {
            return [
                self::shebaCredit(),
                self::cbl($version_code, $platform_name),
                self::bkash(),
                self::ssl()
            ];
        } else if (isset(\request()->type) && \request()->type !== 'customer') {
            return [self::shebaCredit()];
        } else {
            return [
                self::shebaCredit(),
                self::cbl($version_code, $platform_name),
                self::bkash(),
                self::ssl()
            ];
        }
    }

    private static function getUtilityPayments($version_code, $platform_name)
    {
        return [
            self::shebaCredit(),
            self::bkash(),
            self::cbl($version_code, $platform_name),
            self::ssl()
        ];
    }

    private static function getPaymentLinkPayments($version_code, $platform_name)
    {
        return [
            self::bkash(),
            self::cbl($version_code, $platform_name),
            self::ssl()
        ];
    }

    private static function getWalletRechargePayments($version_code, $platform_name)
    {
        return [
            self::bkash(),
            self::cbl($version_code, $platform_name),
            self::ssl()
        ];
    }

    /**
     * @param $version_code
     * @param $platform_name
     * @return array
     */
    private static function cbl($version_code, $platform_name)
    {
        return [
            'name' => 'City Bank',
            'is_published' => self::getCblStatus($version_code, $platform_name),
            'description' => '',
            'asset' => 'cbl',
            'method_name' => 'cbl'
        ];
    }

    private static function getCblStatus($version_code, $platform_name)
    {
        if (!$version_code) return 1;

        return $platform_name && $platform_name == 'ios' ? 1 : ($version_code > 30112 ? 1 : 0);
    }

    /**
     * @return array
     */
    private static function ssl()
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
    private static function shebaCredit()
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
    private static function bkash()
    {
        return [
            'name' => 'bKash',
            'is_published' => 1,
            'description' => '',
            'asset' => 'bkash',
            'method_name' => 'bkash'
        ];
    }
}
