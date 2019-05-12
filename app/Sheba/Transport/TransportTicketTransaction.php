<?php namespace Sheba\Transport;

use App\Models\Transport\TransportTicketOrder;

class TransportTicketTransaction
{
    private $log;
    private $amount;
    /** @var TransportTicketOrder $transportTicketOrder */
    private $transportTicketOrder;
    private $eventType;
    private $eventId;

    public function setMovieTicketOrder(TransportTicketOrder $transport_ticket_order)
    {
        $this->transportTicketOrder = $transport_ticket_order;
        return $this;
    }

    /**
     * @param $amount
     * @return TransportTicketTransaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $log
     * @return TransportTicketTransaction
     */
    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getMovieTicketOrder()
    {
        return $this->transportTicketOrder;
    }

    /**
     * @return mixed
     */
    public function getLog()
    {
        return $this->log;
    }

    public function setEventType($event_type)
    {
        $this->eventType = $event_type;
        return $this;
    }

    public function setEventId($event_id)
    {
        $this->eventId = $event_id;
        return $this;
    }

    public function getEventType()
    {
        return $this->eventType;
    }

    public function getEventId()
    {
        return $this->eventId;
    }
}