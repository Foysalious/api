<?php

namespace Sheba\TopUp\Vendor;

use Sheba\TopUp\TopUpResponse;
use Sheba\TopUp\Vendor\Internal\Rax;

class Robi extends Vendor
{
    private $rax;

    public function __construct(Rax $rax)
    {
        $this->rax = $rax;
    }

    public function recharge($mobile_number, $amount, $type): TopUpResponse
    {
        $mid = config('topup.robi.robi_mid');
        $pin = config('topup.robi.robi_pin');
        return $this->rax->setPin($pin)->setMId($mid)->recharge($mobile_number, $amount, $type);
    }
}