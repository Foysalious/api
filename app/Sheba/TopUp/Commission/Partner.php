<?php namespace Sheba\TopUp\Commission;

use Sheba\TopUp\TopUpCommission;

class Partner extends TopUpCommission
{
    public function disburse()
    {
        $this->storeAgentsCommission();
    }

    public function refund()
    {
        // TODO: Implement refund() method.
    }
}