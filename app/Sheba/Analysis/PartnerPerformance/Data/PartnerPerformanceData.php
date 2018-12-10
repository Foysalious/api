<?php namespace Sheba\Analysis\PartnerPerformance\Data;

class PartnerPerformanceData
{
    private $completed;
    private $no_complain;
    private $timely_accepted;
    private $timely_processed;
    private $order_received;

    /**
     * @param mixed $completed
     * @return PartnerPerformanceData
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
        return $this;
    }

    /**
     * @param mixed $no_complain
     * @return PartnerPerformanceData
     */
    public function setNoComplain($no_complain)
    {
        $this->no_complain = $no_complain;
        return $this;
    }

    /**
     * @param mixed $timely_accepted
     * @return PartnerPerformanceData
     */
    public function setTimelyAccepted($timely_accepted)
    {
        $this->timely_accepted = $timely_accepted;
        return $this;
    }

    /**
     * @param mixed $timely_processed
     * @return PartnerPerformanceData
     */
    public function setTimelyProcessed($timely_processed)
    {
        $this->timely_processed = $timely_processed;
        return $this;
    }

    /**
     * @param mixed $order_received
     * @return PartnerPerformanceData
     */
    public function setOrderReceived($order_received)
    {
        $this->order_received = $order_received;
        return $this;
    }


}