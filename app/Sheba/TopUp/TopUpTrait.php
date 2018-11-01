<?php

namespace Sheba\TopUp;

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
        (new TopUp())->setAgent($this)->setVendor($vendor)->recharge($mobile_number, $amount, $type);
    }

    public function refund($amount, $log)
    {
        $this->creditWallet($amount);
        $this->walletTransaction(['amount' => $amount, 'type' => 'Credit', 'log' => $log]);
    }
}