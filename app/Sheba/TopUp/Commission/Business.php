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

    protected function getBasicTopUpLog()
    {
        return parent::getBasicTopUpLog() . " You have received BDT " . $this->topUpOrder->agent_commission . " cashback on this recharge.";
    }
}