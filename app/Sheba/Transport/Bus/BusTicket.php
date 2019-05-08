<?php namespace Sheba\Transport\Bus;

use Sheba\Transport\TransportAgent;

class BusTicket
{
    /** @var TransportAgent $agent */
    private $agent;

    /**
     * @param TransportAgent $agent
     * @return $this
     */
    public function setAgent(TransportAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    public function disburseCommissions()
    {
        $this->agent->getBusTicketCommission()->disburse();
        return $this;
    }

    public function agentTransaction()
    {
        // $this->agent;
    }
}