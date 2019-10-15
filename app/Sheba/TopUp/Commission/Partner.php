<?php namespace Sheba\TopUp\Commission;

use Sheba\TopUp\TopUpCommission;

class Partner extends TopUpCommission
{
    public function disburse()
    {
        $this->storeAgentsCommission();
        $this->storeExpenseIncome();
    }

    private function storeExpenseIncome()
    {
        $income = $this->topUpOrder->agent_commission;
        $cost = $this->amount;
        $partner = $this->agent;
    }

    public function refund()
    {
        $this->refundAgentsCommission();
    }
}
