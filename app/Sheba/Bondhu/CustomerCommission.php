<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 12/4/2018
 * Time: 11:22 AM
 */

namespace App\Sheba\Bondhu;


use App\Models\TopUpOrder;
use Sheba\TopUp\TopUpAgent;

class CustomerCommission extends TopUpCommission
{

    private $topUpOrder;
    private $agent;

    public function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
    }

    public function setTopUpOrder(TopUpOrder $topUpOrder)
    {
        $this->topUpOrder = $topUpOrder;
    }

    private function calculateAgentCommission()
    {

    }

    public function disburse()
    {
        // TODO: Implement disburse() method.
    }
}