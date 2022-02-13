<?php

namespace Sheba\ResellerPayment\Statics;

use Sheba\MerchantEnrollment\Statics\MEFGeneralStatics;

class ResellerPaymentGeneralStatic
{
    public static function notificationSubmitValidation(): array
    {
        return [
            'key' => 'required|in:'. implode(',', MEFGeneralStatics::payment_gateway_keys()),
            'new_status' => 'required|in:processing,verified,rejected',
            'partner_id' => 'required'
        ];
    }

    public static function smsSendValidation(): array
    {
        return  [
            'key' => 'required|in:'. implode(',', MEFGeneralStatics::payment_gateway_keys()),
            'partner_id' => 'required',
            "sms_body" => 'required'
        ];
    }
}