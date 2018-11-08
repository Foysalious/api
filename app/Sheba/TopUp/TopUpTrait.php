<?php namespace Sheba\TopUp;

use App\Models\TopUpVendor;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\Vendor\VendorFactory;

trait TopUpTrait
{
    /**
     * @param $vendor_id
     * @param $mobile_number
     * @param $amount
     * @param $type
     * @throws \Exception
     */
    public function doRecharge($vendor_id, $mobile_number, $amount, $type)
    {
        $vendor = (new VendorFactory())->getById($vendor_id);
        /** @var $this TopUpAgent */
        (new TopUp())->setAgent($this)->setVendor($vendor)->recharge($mobile_number, $amount, $type);
    }

    public function refund($amount, $log)
    {
        $this->creditWallet($amount);
        $this->walletTransaction(['amount' => $amount, 'type' => 'Credit', 'log' => $log]);
    }

    public function calculateCommission($amount, TopUpVendor $topup_vendor)
    {
        return (double)$amount * ($this->agentCommission($topup_vendor) / 100);
    }

    public function agentCommission($topup_vendor)
    {
        return (double)$topup_vendor->commissions()->where('type', get_class($this))->first()->agent_commission;
    }
}