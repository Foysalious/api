<?php namespace Sheba\Transport\Bus\Order;

use Sheba\Transport\Bus\Repositories\TransportTicketOrdersRepository;

class Creator
{
    /** @var TransportTicketOrdersRepository $ordersRepo */
    private $ordersRepo;
    /** @var TransportTicketRequest $transportTicketRequest */
    private $transportTicketRequest;

    public function __construct(TransportTicketOrdersRepository $orders_repo)
    {
        $this->ordersRepo = $orders_repo;
    }

    /**
     * @param TransportTicketRequest $request
     * @return Creator
     */
    public function setRequest(TransportTicketRequest $request)
    {
        $this->transportTicketRequest = $request;
        return $this;
    }

    public function create()
    {
        $data = [
            'agent_type' => $this->transportTicketRequest->getAgentType(),
            'agent_id' => $this->transportTicketRequest->getAgentId(),
            'reserver_name' => $this->transportTicketRequest->getReserverName(),
            'reserver_mobile' => $this->transportTicketRequest->getReserverMobile(),
            'reserver_email' => $this->transportTicketRequest->getReserverEmail(),
            'vendor_id' => $this->transportTicketRequest->getVendorId(),
            'status' => $this->transportTicketRequest->getStatus(),
            'amount' => $this->transportTicketRequest->getAmount(),
            'voucher_id' => $this->transportTicketRequest->getVoucher() ? $this->transportTicketRequest->getVoucher()->id : null,
            'discount' => $this->transportTicketRequest->getDiscount() ?: 0.00,
            'discount_percent' => $this->transportTicketRequest->getDiscountPercent() ?: 0.00,
            'sheba_contribution' => $this->transportTicketRequest->getShebaContribution() ?: 0.00,
            'vendor_contribution' => $this->transportTicketRequest->getVendorContribution() ?: 0.00,
            'transaction_id' => $this->transportTicketRequest->getTransactionId(),
            'journey_date' => $this->transportTicketRequest->getJourneyDate(),
            'departure_time' => $this->transportTicketRequest->getDepartureTime(),
            'arrival_time' => $this->transportTicketRequest->getArrivalTime(),
            'departure_station_name' => $this->transportTicketRequest->getDepartureStationName(),
            'arrival_station_name' => $this->transportTicketRequest->getArrivalStationName(),
            'reservation_details' => $this->transportTicketRequest->getReservationDetails()
        ];
        $order = $this->ordersRepo->save($data);

        return $order;
    }
}