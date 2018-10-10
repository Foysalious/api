<?php

namespace Sheba\TopUp\Vendor;

use Sheba\TopUp\Vendor\Response\TopUpResponse;

class Mock extends Vendor
{
    public function recharge($mobile_number, $amount, $type): TopUpResponse
    {
    }
}