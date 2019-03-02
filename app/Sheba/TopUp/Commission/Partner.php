<?php namespace Sheba\TopUp\Commission;

use Sheba\TopUp\MovieTicketCommission;

class Partner extends MovieTicketCommission
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