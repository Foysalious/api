<?php

namespace Sheba;

use Sheba\TopUp\Mock;
use Sheba\TopUp\Robi;
use Sheba\TopUp\TopUp;

trait TopUpTrait
{
    public function doRecharge($vendor_id, $mobile_number, $amount, $type)
    {
        return $this->getTopUp($vendor_id)->recharge($mobile_number, $amount, $type);
    }

    private function getTopUp($vendor_id)
    {
        if ($vendor_id == 1)
            return new TopUp($this, new Mock());
        elseif ($vendor_id == 2) {
            return new TopUp($this, new Robi());
        }
    }
}