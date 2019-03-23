<?php namespace Sheba\TopUp\Commission;


use Sheba\TopUp\TopUpCommission;

class Vendor extends TopUpCommission
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