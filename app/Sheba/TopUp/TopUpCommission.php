<?php namespace Sheba\TopUp;

use App\Models\TopUpOrder;
use App\Models\TopUpVendor;

abstract class TopUpCommission
{
    protected $topUpOrder;
    protected $agent;
    protected $vendor;
    protected $amount;

    /**
     * @param TopUpAgent $agent
     */
    protected function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
    }

    /**
     * @param TopUpOrder $topUpOrder
     * @return $this
     */
    public function setTopUpOrder(TopUpOrder $topUpOrder)
    {
        $this->topUpOrder = $topUpOrder;
        $this->amount = $this->topUpOrder->amount;

        $this->setAgent($topUpOrder->agent);
        $this->setTopUpVendor($topUpOrder->vendor);

        unset($topUpOrder->agent);
        unset($topUpOrder->vendor);

        return $this;
    }

    /**
     * @param TopUpVendor $topUpVendor
     */
    protected function setTopUpVendor(TopUpVendor $topUpVendor)
    {
        $this->vendor = $topUpVendor;
    }

    protected function storeAgentsCommission()
    {
        $this->topUpOrder->agent_commission =  $this->calculateCommission($this->topUpOrder->amount, $this->vendor);
        $this->topUpOrder->save();
    }

    /**
     * @param $amount
     * @param TopUpVendor $topup_vendor
     * @return float|int
     */
    protected function calculateCommission($amount, TopUpVendor $topup_vendor)
    {
        return (double)$amount * ($this->getVendorAgentCommission($topup_vendor) / 100);
    }

    /**
     * @param $amount
     * @param TopUpVendor $topup_vendor
     * @return float|int
     */
    protected function calculateAmbassadorCommission($amount, TopUpVendor $topup_vendor)
    {
        return (double)$amount * ($this->getVendorAmbassadorCommission($topup_vendor) / 100);
    }

    /**
     * @param $topup_vendor
     * @return float
     */
    private function getVendorAgentCommission($topup_vendor)
    {
        return (double)$topup_vendor->commissions()->where('type', get_class($this->agent))->first()->agent_commission;
    }

    /**
     * @param $topup_vendor
     * @return float
     */
    private function getVendorAmbassadorCommission($topup_vendor)
    {
        return (double)$topup_vendor->commissions()->where('type', get_class($this->agent))->first()->ambassador_commission;
    }

    protected function refundAgentsCommission()
    {
        $amount = $this->topUpOrder->amount;
        $amount_after_commission = round($amount - $this->calculateCommission($amount, $this->topUpOrder->vendor), 2);
        $log = "Your recharge TK $amount to {$this->topUpOrder->payee_mobile} has failed, TK $amount_after_commission is refunded in your account.";
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