<?php namespace Sheba\TopUp;

use App\Models\TopUpOrder;
use App\Models\TopUpVendor;

use App\Models\TopUpVendorCommission;
use Sheba\ModificationFields;

abstract class TopUpCommission
{
    use ModificationFields;

    /** @var TopUpOrder */
    protected $topUpOrder;
    /** @var TopUpAgent */
    protected $agent;
    /** @var TopUpVendor */
    protected $vendor;
    /** @var TopUpVendorCommission */
    protected $vendorCommission;
    protected $amount;

    /**
     * @param TopUpAgent $agent
     * @return TopUpCommission
     */
    protected function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * @param TopUpOrder $top_up_order
     * @return $this
     */
    public function setTopUpOrder(TopUpOrder $top_up_order)
    {
        $this->topUpOrder = $top_up_order;
        $this->amount = $this->topUpOrder->amount;

        $this->setAgent($top_up_order->agent)->setTopUpVendor($top_up_order->vendor)->setVendorCommission();

        unset($top_up_order->agent);
        unset($top_up_order->vendor);

        return $this;
    }

    /**
     * @param TopUpVendor $top_up_order
     * @return TopUpCommission
     */
    protected function setTopUpVendor(TopUpVendor $top_up_order)
    {
        $this->vendor = $top_up_order;
        return $this;
    }

    /**
     * @return TopUpCommission
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
        $this->topUpOrder->agent_commission = $this->calculateCommission($this->topUpOrder->amount);
        $this->topUpOrder->save();

        $transaction = (new TopUpTransaction())->setAmount($this->amount - $this->topUpOrder->agent_commission)
            ->setLog($this->amount . " has been topped up to " . $this->topUpOrder->payee_mobile)
            ->setTopUpOrder($this->topUpOrder);
        $this->agent->topUpTransaction($transaction);
    }

    /**
     * @param $amount
     * @return float|int
     */
    protected function calculateCommission($amount)
    {
        return (double)$amount * ($this->getVendorAgentCommission() / 100);
    }

    /**
     * @param $amount
     * @return float|int
     */
    protected function calculateAmbassadorCommission($amount)
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
        $amount = $this->topUpOrder->amount;
        $amount_after_commission = round($amount - $this->calculateCommission($amount), 2);
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