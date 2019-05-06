<?php namespace Sheba\Transport\Bus\Vendor\BdTickets;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Sheba\Transport\Bus\Order\Creator;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;
use Sheba\Transport\Bus\Vendor\Vendor;
use Sheba\Transport\Bus\ClientCalls\BdTickets as BdTicketsClientCall;

class BdTickets extends Vendor
{
    const BOOK_APPLICATION = 'BUS';
    const APPLICATION_CHANNEL = 'REMOTE';
    const ACCOUNT_TYPE = 'AGENT';

    /** @var BdTicketsClientCall $bdTicketClient */
    private $bdTicketClient;
    /** @var Creator $creator */
    private $creator;

    public function __construct(BdTicketsClientCall $bd_ticket_client, BusRouteLocationRepository $bus_route_location_repo)
    {
        parent::__construct($bus_route_location_repo);
        $this->bdTicketClient = $bd_ticket_client;
    }

    /**
     * @param Creator $creator
     * @throws GuzzleException
     */
    public function bookTicket(Creator $creator)
    {
        $this->creator = $creator;
        $cart_id = $this->createCart();
        $this->updateCart($cart_id);
        $this->_bookTicket($cart_id);
    }

    /**
     * CREATE EMPTY CART FOR TICKET PLACE HOLDER
     *
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    private function createCart()
    {
        $data = ['bookApplication' => self::BOOK_APPLICATION, 'applicationChannel' => self::APPLICATION_CHANNEL];
        $response = $this->bdTicketClient->post('carts', $data);
        return $response['data']['id'];
    }

    private function updateCart($cart_id)
    {
        $data = [
            "cartType" => "DEPARTURE",
            "boardingPoint" => (int)$this->creator->getBoardingPoint(),
            "droppingPoint" => (int)$this->creator->getDroppingPoint(),
            "coachId" => $this->creator->getCoachId(),
            "passengerList" => [
                json_encode([
                    "firstName" => $this->creator->getReserverName(),
                    "lastName" => "",
                    "phoneNumber" => $this->creator->getReserverMobile(),
                    "email" => $this->creator->getReserverEmail(),
                    "gender" => strtoupper($this->creator->getReserverGender()[0])
                ])
            ],
            "seatIdList" => $this->creator->getSeatIdList(),
            "applicationChannel" => self::APPLICATION_CHANNEL
        ];
        
        return $this->bdTicketClient->put("carts/$cart_id", $data);
    }

    private function _bookTicket($cart_id)
    {
        $data = ['cartId' => $cart_id, 'applicationChannel' => self::APPLICATION_CHANNEL];
        return $this->bdTicketClient->post('carts/book', $data);
    }

    public function confirmTicket($ticket_id)
    {
        $data = ['ticketId' => $ticket_id, 'accountType' => self::ACCOUNT_TYPE, 'applicationChannel' => self::APPLICATION_CHANNEL];
        return $this->bdTicketClient->post('tickets/confirm', $data);
    }
}