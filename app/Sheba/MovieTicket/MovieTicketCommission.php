<?php namespace Sheba\MovieTicket;

use App\Models\MovieTicketOrder;
use App\Models\MovieTicketVendor;
use Sheba\ModificationFields;

abstract class MovieTicketCommission
{
    use ModificationFields;

    /** @var MovieTicketOrder */
    protected $movieTicketOrder;
    /** @var MovieAgent */
    protected $agent;
    /** @var MovieTicketVendor */
    protected $vendor;
    /** @var MovieTicketCommission */
    protected $vendorCommission;
    protected $amount;

    /**
     * @param MovieAgent $agent
     * @return MovieTicketCommission
     */
    protected function setAgent(MovieAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * @param MovieTicketOrder $movie_ticket_order
     * @return $this
     */
    public function setMovieTicketOrder(MovieTicketOrder $movie_ticket_order)
    {
        $this->movieTicketOrder = $movie_ticket_order;
        $this->amount = $this->movieTicketOrder->amount;

        $this->setAgent($movie_ticket_order->agent)->setMovieTicketVendor($movie_ticket_order->vendor)->setVendorCommission();

        unset($movie_ticket_order->agent);
        unset($movie_ticket_order->vendor);

        return $this;
    }

    /**
     * @param MovieTicketOrder $movieTicketOrder
     * @return MovieTicketCommission
     */
    protected function setMovieTicketVendor(MovieTicketOrder $movieTicketOrder)
    {
        $this->vendor = $movieTicketOrder;
        return $this;
    }

    /**
     * @return MovieTicketCommission
     */
    protected function setVendorCommission()
    {
        $commissions = $this->vendor->commissions()->where('type', get_class($this->agent));
        $commissions_copy = clone $commissions;
        $commission_of_individual = $commissions_copy->where('type_id', $this->agent->id)->first();
        $this->vendorCommission = $commission_of_individual ?: $commissions->whereNull('type_id')->first();
        return $this;
    }

    /**
     *
     */
    protected function storeAgentsCommission()
    {
        $this->movieTicketOrder->agent_commission = $this->calculateMovieTicketCommission($this->movieTicketOrder->amount);
        $this->movieTicketOrder->save();

        $transaction = (new MovieTicketTransaction())->setAmount($this->amount - $this->movieTicketOrder->agent_commission)
            ->setLog($this->amount . " has been topped up to " . $this->movieTicketOrder->payee_mobile)
            ->setMovieTicketOrder($this->movieTicketOrder);
        $this->agent->movieTicketTransaction($transaction);
    }

    /**
     * @param $amount
     * @return float|int
     */
    protected function calculateMovieTicketCommission($amount)
    {
        return (double)$amount * ($this->getVendorAgentCommission() / 100);
    }

    /**
     * @param $amount
     * @return float|int
     */
    protected function calculateAmbassadorCommissionForMovieTicket($amount)
    {
        return (double)$amount * ($this->getVendorAmbassadorCommission() / 100);
    }

    /**
     * @return float
     */
    private function getVendorAgentCommission()
    {
        return (double)$this->vendorCommission->agent_commission;
    }

    /**
     * @return float
     */
    private function getVendorAmbassadorCommission()
    {
        return (double)$this->vendorCommission->ambassador_commission;
    }

    protected function refundAgentsCommission()
    {
        $this->setModifier($this->agent);
        $amount = $this->movieTicketOrder->amount;
        $amount_after_commission = round($amount - $this->calculateMovieTicketCommission($amount), 2);
        $log = "Your recharge TK $amount to {$this->movieTicketOrder->payee_mobile} has failed, TK $amount_after_commission is refunded in your account.";
        $this->refundUser($amount_after_commission, $log);
    }

    private function refundUser($amount, $log)
    {
        $this->agent->creditWallet($amount);
        $this->agent->walletTransaction(['amount' => $amount, 'type' => 'Credit', 'log' => $log]);
    }

    abstract public function disburse();

    abstract public function refund();
}