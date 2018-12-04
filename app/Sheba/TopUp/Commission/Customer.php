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
    public function disburse()
    {
        $this->topUpOrder->agent_commission =  $this->calculateCommission($this->topUpOrder->amount, $this->vendor);
        $this->topUpOrder->save();
    }
}