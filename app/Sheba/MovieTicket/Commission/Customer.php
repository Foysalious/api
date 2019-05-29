<?php namespace Sheba\MovieTicket\Commission;

use Sheba\MovieTicket\MovieTicketCommission;

class Customer extends MovieTicketCommission
{
    public function disburse()
    {
    }

    public function refund()
    {
        $this->refundAgentsCommission();
    }
}