<?php namespace App\Sheba\TopUp;

use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use Sheba\TopUp\TopUpAgent;

abstract class TopUpCommission
{

    protected $topUpOrder;
    protected $agent;
    protected $vendor;
    protected $amount;

    public function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    public function setTopUpOrder(TopUpOrder $topUpOrder)
    {
        $this->topUpOrder = $topUpOrder;
        return $this;
    }

    public function setTopUpVendor(TopUpVendor $topUpVendor)
    {
        $this->vendor = $topUpVendor;
        return $this;
    }

    public function calculateCommission($amount, TopUpVendor $topup_vendor)
    {
        return (double)$amount * ($this->agentCommission($topup_vendor) / 100);
    }

    public function agentCommission($topup_vendor)
    {
        return (double)$topup_vendor->commissions()->where('type', get_class($this))->first()->agent_commission;
    }

    abstract public function disburse();
}