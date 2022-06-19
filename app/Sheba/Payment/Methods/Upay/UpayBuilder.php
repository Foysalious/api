<?php

namespace Sheba\Payment\Methods\Upay;

use App\Models\Payable;
use Sheba\Payment\Methods\Upay\Stores\DefaultUpayStore;

class UpayBuilder
{
    public static function get(Payable $payable)
    {
        /** @var Upay $upay */
        $upay = app(Upay::class);
        return $upay->setStore(new DefaultUpayStore());
    }

}