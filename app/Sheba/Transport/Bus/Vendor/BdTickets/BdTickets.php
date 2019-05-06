<?php namespace Sheba\Transport\Bus\Vendor\BdTickets;

use Sheba\Transport\Bus\Vendor\Vendor;
use Sheba\Transport\Bus\ClientCalls\BdTickets as BdTicketsClientCall;

class BdTickets extends Vendor
{
    const BOOK_APPLICATION = 'BUS';
    const APPLICATION_CHANNEL = 'REMOTE';

    /** @var BdTicketsClientCall $bdTicketClient */
    private $bdTicketClient;

    public function __construct(BdTicketsClientCall $bd_ticket_client)
    {
        $this->bdTicketClient = $bd_ticket_client;
    }

    public function bookTicket()
    {
        $this->createCart();
        $this->updateCart();
        $this->_bookTicket();
    }

    private function createCart()
    {
        $data = [
            'bookApplication' => self::BOOK_APPLICATION,
            'applicationChannel' => self::APPLICATION_CHANNEL
        ];
        return $this->bdTicketClient->post('carts', $data);
    }

    private function updateCart()
    {
    }

    private function _bookTicket()
    {
    }
}