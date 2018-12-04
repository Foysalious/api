<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 12/4/2018
 * Time: 11:22 AM
 */

namespace App\Sheba\TopUp\Commission;

use App\Models\TopUpOrder;
use App\Sheba\TopUp\TopUpCommission;
use Sheba\TopUp\TopUpAgent;

class Customer extends TopUpCommission
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

    public function setTopUpVendor($topUpVendor)
    {
        $this->vendor = $topUpVendor;
    }

    public function disburse()
    {
        $this->topUpOrder->agent_commission =  $this->agent->calculateCommission($this->topUpOrder->amount, $this->vendor);
    }
}