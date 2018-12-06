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
     * @return $this
     */
    public function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * @param TopUpOrder $topUpOrder
     * @return $this
     */
    public function setTopUpOrder(TopUpOrder $topUpOrder)
    {
        $this->topUpOrder = $topUpOrder;
        return $this;
    }

    /**
     * @param TopUpVendor $topUpVendor
     * @return $this
     */
    public function setTopUpVendor(TopUpVendor $topUpVendor)
    {
        $this->vendor = $topUpVendor;
        return $this;
    }

    public function storeAgentsCommission()
    {
        $this->topUpOrder->agent_commission =  $this->calculateCommission($this->topUpOrder->amount, $this->vendor);
        $this->topUpOrder->save();
    }

    /**
     * @param $amount
     * @param TopUpVendor $topup_vendor
     * @return float|int
     */
    public function calculateCommission($amount, TopUpVendor $topup_vendor)
    {
        return (double)$amount * ($this->getVendorAgentCommission($topup_vendor) / 100);
    }

    /**
     * @param $amount
     * @param TopUpVendor $topup_vendor
     * @return float|int
     */
    public function calculateAmbassadorCommission($amount, TopUpVendor $topup_vendor)
    {
        return (double)$amount * ($this->getVendorAmbassadorCommission($topup_vendor) / 100);
    }

    /**
     * @param $topup_vendor
     * @return float
     */
    public function getVendorAgentCommission($topup_vendor)
    {
        return (double)$topup_vendor->commissions()->where('type', get_class($this->agent))->first()->agent_commission;
    }

    /**
     * @param $topup_vendor
     * @return float
     */
    public function getVendorAmbassadorCommission($topup_vendor)
    {
        return (double)$topup_vendor->commissions()->where('type', get_class($this->agent))->first()->ambassador_commission;
    }

    abstract public function disburse();
}