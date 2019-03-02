<?php namespace Sheba\TopUp\Commission;

use Sheba\TopUp\MovieTicketCommission;

class Customer extends MovieTicketCommission
{
    public function disburse()
    {
       $this->storeAgentsCommission();
    }

    public function refund()
    {
        $this->refundAgentsCommission();
    }
}