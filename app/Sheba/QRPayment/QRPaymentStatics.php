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
            'customer_id'    => 'required',
            "payment_method" => 'required'
        ];
    }
}