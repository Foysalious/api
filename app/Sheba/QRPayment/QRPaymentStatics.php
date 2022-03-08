<?php

namespace App\Sheba\QRPayment;

class QRPaymentStatics
{
    const MTB_VALIDATE_URL = "retailfin/wqr/api/gettxndata?";

    public static function gatewayVisibleKeys(): array
    {
        return ['name', 'name_bn', 'asset', 'method_name', 'icon'];
    }

    public static function getValidationForQrGenerate(): array
    {
        return [
            "type" => 'required|in:pos_order,accounting_due',
            "type_id" => "required",
            'amount' => 'required|numeric',
            'payer_id' => 'required',
            'payer_type' => 'required|in:pos_customer,supplier',
            "payment_method" => 'required'
        ];
    }

    public static function qrGenerateKeys(): array
    {
        return array_keys(self::getValidationForQrGenerate());
    }

    public static function getValidationForValidatePayment(): array
    {
        return [
            "qr_id" => "sometimes",
            "merchant_id" => "required",
            "amount" => "required",
            "status" => "sometimes"
        ];
    }
}