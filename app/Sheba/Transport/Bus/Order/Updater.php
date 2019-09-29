<?php namespace Sheba\Transport\Bus\Order;

use App\Models\Transport\TransportTicketOrder;
use Sheba\Transport\Bus\Repositories\TransportTicketOrdersRepository;

class Updater
{
    /** @var TransportTicketOrdersRepository $ordersRepo */
    private $ordersRepo;
    /** @var TransportTicketRequest $transportTicketRequest */
    private $transportTicketRequest;
    /** @var TransportTicketOrder $transportTicketOrder */
    private $transportTicketOrder;

    public function __construct(TransportTicketOrdersRepository $orders_repo)
    {
        $this->ordersRepo = $orders_repo;
    }

    /**
     * @param TransportTicketRequest $request
     * @return Updater
     */
    public function setRequest(TransportTicketRequest $request)
    {
        $this->transportTicketRequest = $request;
        return $this;
    }

    public function setOrder(TransportTicketOrder $transport_ticket_order)
    {
        $this->transportTicketOrder = $transport_ticket_order;
        return $this;
    }

    public function update()
    {
        $data = [
            'status' => $this->transportTicketRequest->getStatus(),
            'sheba_amount' => $this->transportTicketRequest->getShebaAmount() ?: 0.00
        ];
        $order = $this->ordersRepo->update($this->transportTicketOrder, $data);

        return $order;
    }
}