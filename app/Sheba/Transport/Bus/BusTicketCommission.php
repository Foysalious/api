<?php namespace Sheba\Transport\Bus;

use App\Models\Transport\TransportTicketOrder;
use App\Models\Transport\TransportTicketVendor;
use Sheba\Transport\TransportAgent;
use Sheba\Transport\TransportTicketTransaction;

abstract class BusTicketCommission
{
    /** @var TransportTicketOrder */
    protected $transportTicketOrder;
    /** @var TransportAgent $agent */
    protected $agent;
    /** @var TransportTicketVendor $vendor */
    protected $vendor;
    protected $vendorCommission;
    protected $transaction;

    abstract public function disburse();

    abstract public function refund();

    /**
     * @param TransportAgent $agent
     * @return $this
     */
    protected function setAgent(TransportAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    public function setTransportTicketOrder(TransportTicketOrder $transport_ticket_order)
    {
        $this->transportTicketOrder = $transport_ticket_order;
        $this->amount = $this->transportTicketOrder->amount;
        $this->setAgent($transport_ticket_order->agent)
            ->setTransportTicketVendor($transport_ticket_order->vendor)
            ->setVendorCommission();

        return $this;
    }

    protected function storeAgentsCommission()
    {
        $this->transportTicketOrder->agent_amount = $this->getVendorAgentCommission();
        $this->transportTicketOrder->save();

        if ($this->transportTicketOrder->agent_amount > 0) {
            $log = number_format(($this->transportTicketOrder->agent_amount), 2) . " TK has been collected from Sheba for transport ticket sales commission, of user with mobile number: " . $this->transportTicketOrder->reserver_mobile;
            $transaction = (new TransportTicketTransaction())
                ->setAmount($this->transportTicketOrder->agent_amount)
                ->setLog($log)
                ->setTransportTicketOrder($this->transportTicketOrder);
            $this->transaction = $this->agent->transportTicketTransaction($transaction);
        }
    }

    public function getTransaction()
    {
        return $this->transaction;
    }

    protected function setTransportTicketVendor(TransportTicketVendor $transport_ticket_vendor)
    {
        $this->vendor = $transport_ticket_vendor;
        return $this;
    }

    protected function setVendorCommission()
    {
        $commissions = $this->vendor->commissions()->where('type', get_class($this->agent));
        $commissions_copy = clone $commissions;
        $commission_of_individual = $commissions_copy->where('type_id', $this->agent->id)->first();
        $this->vendorCommission = $commission_of_individual ?: $commissions->whereNull('type_id')->first();
        return $this;
    }

    /**
     * @return float|int
     */
    protected function calculateTransportTicketCommission()
    {
        return (double)($this->getShebaCommission() - $this->getVendorAgentCommission());
    }

    private function getShebaCommission()
    {
        return (double)$this->vendor->sheba_amount;
    }

    /**
     * @return float
     */
    private function getVendorAgentCommission()
    {
        return (double)$this->vendorCommission->agent_amount;
    }

    /**
     * @return float|int
     */
    protected function calculateAmbassadorCommissionForTransportTicket()
    {
        return (double)($this->getShebaCommission() - $this->getVendorAmbassadorCommission());
    }

    /**
     * @return float
     */
    private function getVendorAmbassadorCommission()
    {
        return (double)$this->vendorCommission->ambassador_amount;
    }
}
