<?php

namespace App\Sheba\QRPayment;

class QRPaymentStatics
{
    public static function gatewayVisibleKeys(): array
    {
        return ['name', 'name_bn', 'asset', 'method_name', 'icon'];
    }
}