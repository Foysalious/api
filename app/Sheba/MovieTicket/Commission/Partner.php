<?php namespace Sheba\MovieTicket\Commission;

use Sheba\MovieTicket\MovieTicketCommission;
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