<?php namespace Sheba\Transport\Bus;

use App\Models\Transport\TransportTicketOrder;
use App\Models\Transport\TransportTicketVendor;
use Sheba\Transport\TransportAgent;
use Sheba\Transport\TransportTicketTransaction;

class BusTicket
{
    /** @var TransportAgent $agent */
    private $agent;
    /** @var TransportTicketOrder $transportTicketOrder */
    private $transportTicketOrder;

    /**
     * @param TransportAgent $agent
     * @return $this
     */
    public function setAgent(TransportAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    public function setOrder(TransportTicketOrder $transport_ticket_order)
    {
        $this->transportTicketOrder = $transport_ticket_order;
        return $this;
    }

    public function disburseCommissions()
    {
        $this->agent->getBusTicketCommission()->setTransportTicketOrder($this->transportTicketOrder)->disburse();
    }

    public function agentTransaction()
    {
        $log = $this->transportTicketOrder->amount . " has been deducted for a transport ticket purchase, of user with mobile number: " . $this->transportTicketOrder->reserver_mobile;
        $transaction = (new TransportTicketTransaction())
            ->setEventType(get_class($this->transportTicketOrder))
            ->setEventId($this->transportTicketOrder->id)
            ->setAmount($this->transportTicketOrder->amount)
            ->setLog($log)
            ->setTransportTicketOrder($this->transportTicketOrder);

        $this->agent->transportTicketTransaction($transaction);
    }
}