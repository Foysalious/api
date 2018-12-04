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

    public function storeAgentsCommission()
    {
        $this->topUpOrder->agent_commission =  $this->agent->calculateCommission($this->topUpOrder->amount, $this->vendor);
        $this->topUpOrder->save();
    }

    abstract public function disburse();
}