<?php namespace Sheba\TopUp\Commission;

use Sheba\TopUp\TopUpCommission;

class Partner extends TopUpCommission
{
    public function disburse()
    {
        $this->topUpOrder->agent_commission =  $this->agent->calculateCommission($this->topUpOrder->amount, $this->vendor);
        $this->topUpOrder->save();
    }
}