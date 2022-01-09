<?php

namespace Sheba\MerchantEnrollment\Statics;

use Sheba\ResellerPayment\Exceptions\InvalidKeyException;

class PaymentMethodStatics
{
    public static function classMap(): array
    {
        return [
            'ssl'   => 'SslGateway',
            'bkash' => 'BkashGateway'
        ];
    }

    /**
     * @param $paymentGatewayCode
     * @return mixed
     * @throws InvalidKeyException
     */
    public static function paymentGatewayCategoryList($paymentGatewayCode)
    {
        $categoryList = config('reseller_payment.category_list');
        if (isset($categoryList[$paymentGatewayCode])) return $categoryList[$paymentGatewayCode];
        throw new InvalidKeyException();
    }

    public static function categoryTitles($category_code)
    {
        $titles = config('reseller_payment.category_titles');
        if (isset($titles[$category_code])) return $titles[$category_code];
        return ['en' => '', 'bn' => ''];
    }

    /**
     * @param $paymentGatewayKey
     * @return mixed
     * @throws InvalidKeyException
     */
    public static function paymentMethodWiseExcludedKeys($paymentGatewayKey)
    {
        $categoryList = config('reseller_payment.exclude_form_keys');
        if (isset($categoryList[$paymentGatewayKey])) return $categoryList[$paymentGatewayKey];
        throw new InvalidKeyException();
    }
}