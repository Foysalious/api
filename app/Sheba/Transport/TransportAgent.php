<?php namespace Sheba\Transport;

use Sheba\Transport\Bus\BusTicketCommission;

interface TransportAgent
{
    public function transportTicketTransaction(TransportTicketTransaction $transaction);

    /**
     * @return BusTicketCommission
     */
    public function getBusTicketCommission();

    public function transportTicketOrders();
}
