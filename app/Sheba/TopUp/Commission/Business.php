<?php namespace Sheba\TopUp\Commission;

use Sheba\TopUp\TopUpCommission;

class Business extends TopUpCommission
{
    public function disburse()
    {
        $this->storeAgentsCommission();
    }

    public function refund()
    {
        $this->refundAgentsCommission();
    }
}