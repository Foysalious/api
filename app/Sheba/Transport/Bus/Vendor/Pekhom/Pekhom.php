<?php namespace Sheba\Transport\Bus\Vendor\Pekhom;

use Sheba\Transport\Bus\Order\TransportTicketRequest;
use Sheba\Transport\Bus\Vendor\Vendor;

class Pekhom extends Vendor
{
    public function bookTicket(TransportTicketRequest $request)
    {
        // TODO: Implement book() method.
        return null;
    }

    function confirmTicket($ticket_id)
    {
        // TODO: Implement confirmTicket() method.
    }

    public function balance()
    {

    }
}
