<?php namespace Sheba\Transport\Bus\Vendor\BdTickets;

use Sheba\Transport\Bus\Vendor\Vendor;
use Sheba\Transport\Bus\ClientCalls\Busbd as BusbdClientCall;

class BdTickets extends Vendor
{
    const BOOK_APPLICATION = 'BUS';
    const APPLICATION_CHANNEL = 'REMOTE';

    /** @var BusbdClientCall $busbdClient */
    private $busbdClient;

    public function __construct(BusbdClientCall $busbd_client)
    {
        $this->busbdClient = $busbd_client;
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
        return $this->busbdClient->post('carts', $data);
    }

    private function updateCart()
    {
    }

    private function _bookTicket()
    {
    }
}