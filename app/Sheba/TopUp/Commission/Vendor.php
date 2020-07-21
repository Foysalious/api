<?php namespace Sheba\TopUp\Commission;

use Sheba\TopUp\TopUpCommission;
use Sheba\TopUp\TopUpTransaction;

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

    protected function storeAgentsCommission()
    {
        $this->topUpOrder->agent_commission = $this->calculateCommission($this->topUpOrder->amount);
        $this->topUpOrder->save();

        $transaction = (new TopUpTransaction())
            ->setAmount($this->amount - $this->topUpOrder->agent_commission)
            ->setLog($this->amount . " has been topped up to " . $this->topUpOrder->payee_mobile)
            ->setTopUpOrder($this->topUpOrder);

        $this->agent->topUpTransaction($transaction);
    }
}
