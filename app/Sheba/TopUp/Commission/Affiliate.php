<?php namespace App\Sheba\TopUp\Commission;

use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use App\Sheba\TopUp\TopUpCommission;
use Sheba\TopUp\TopUpAgent;

class Affiliate extends TopUpCommission
{

    private $topUpOrder;
    private $agent;
    private $vendor;
    private $amount;

    public function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
    }

    public function setTopUpOrder(TopUpOrder $topUpOrder)
    {
        $this->topUpOrder = $topUpOrder;
    }

    public function setTopUpVendor(TopUpVendor $topUpVendor)
    {
        $this->vendor = $topUpVendor;
    }

    public function disburse()
    {
        $this->topUpOrder->agent_commission =  $this->agent->calculateCommission($this->topUpOrder->amount, $this->vendor);
        $this->topUpOrder->save();
    }
}