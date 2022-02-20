<?php

namespace App\Sheba\QRPayment;

class QRPaymentStatics
{
    public static function gatewayVisibleKeys(): array
    {
        return ['name', 'name_bn', 'asset', 'method_name', 'icon'];
    }

    public static function getValidationForQrGenerate(): array
    {
        return [
            "payable_type"   => 'required|in:pos_order,accounting_due',
            "type_id"        => "required",
            'amount'         => 'required|numeric',
            'payer_id'       => 'required',
            'payer_type'     => 'required|in:pos_customer,supplier',
            "payment_method" => 'required'
        ];
    }

    public static function qrGeenerateKeys(): array
    {
        return array_keys(self::getValidationForQrGenerate());
    }
}