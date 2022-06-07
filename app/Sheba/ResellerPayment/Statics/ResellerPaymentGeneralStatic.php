<?php

namespace Sheba\ResellerPayment\Statics;

class ResellerPaymentGeneralStatic
{
    const OLD_BASE_URL = "v1/partners/merchant-on-boarding/category";
    const NEW_BASE_URL = "v1/partners/dynamic-form/section";


    public static function notificationSubmitValidation(): array
    {
        return [
            'key' => 'required|in:'. implode(',', config('reseller_payment.available_payment_gateway_keys')),
            'new_status' => 'required|in:processing,verified,rejected',
            'partner_id' => 'required'
        ];
    }

    public static function smsSendValidation(): array
    {
        return  [
            'key' => 'required|in:'. implode(',', config('reseller_payment.available_payment_gateway_keys')),
            'partner_id' => 'required',
            "sms_body" => 'required'
        ];
    }
}