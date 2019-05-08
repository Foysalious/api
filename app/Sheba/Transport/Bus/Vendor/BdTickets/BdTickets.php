<?php namespace Sheba\Transport\Bus\Vendor\BdTickets;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Sheba\Transport\Bus\Order\Creator;
use Sheba\Transport\Bus\Order\TransportTicketRequest;
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
    /** @var TransportTicketRequest $ticketRequest */
    private $ticketRequest;

    public function __construct(BdTicketsClientCall $bd_ticket_client, BusRouteLocationRepository $bus_route_location_repo)
    {
        parent::__construct($bus_route_location_repo);
        $this->bdTicketClient = $bd_ticket_client;
    }

    /**
     * @param TransportTicketRequest $ticket_request
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function bookTicket(TransportTicketRequest $ticket_request)
    {
        $this->ticketRequest = $ticket_request;
        $cart_id = $this->createCart();
        $this->updateCart($cart_id);
        return $this->_bookTicket($cart_id);
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
            "boardingPoint" => (int)$this->ticketRequest->getBoardingPoint(),
            "droppingPoint" => (int)$this->ticketRequest->getDroppingPoint(),
            "coachId" => $this->ticketRequest->getCoachId(),
            "passengerList" => [
                json_encode([
                    "firstName" => $this->ticketRequest->getReserverName(),
                    "lastName" => "",
                    "phoneNumber" => $this->ticketRequest->getReserverMobile(),
                    "email" => $this->ticketRequest->getReserverEmail(),
                    "gender" => strtoupper($this->ticketRequest->getReserverGender()[0])
                ])
            ],
            "seatIdList" => $this->ticketRequest->getSeatIdList(),
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