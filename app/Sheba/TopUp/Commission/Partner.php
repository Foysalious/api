<?php namespace App\Sheba\TopUp\Commission;

use App\Sheba\TopUp\TopUpCommission;

class Partner extends TopUpCommission
{
    public function disburse()
    {
        $this->topUpOrder->agent_commission =  $this->calculateCommission($this->topUpOrder->amount, $this->vendor);
        $this->topUpOrder->save();
    }
}