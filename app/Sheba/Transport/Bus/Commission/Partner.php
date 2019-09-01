<?php namespace Sheba\Transport\Bus\Commission;

use Sheba\Transport\Bus\BusTicketCommission;

class Partner extends BusTicketCommission
{
    public function disburse()
    {
        $this->storeAgentsCommission();
    }

    public function refund()
    {

    }
}
