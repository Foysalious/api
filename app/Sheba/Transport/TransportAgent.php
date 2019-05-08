<?php namespace Sheba\Transport;

use Sheba\Transport\Bus\BusTicketCommission;

interface TransportAgent
{
    /**
     * @return BusTicketCommission
     */
    public function getBusTicketCommission();
}