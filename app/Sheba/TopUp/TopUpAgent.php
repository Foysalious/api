<?php namespace Sheba\TopUp;

use App\Models\TopUpVendor;

interface TopUpAgent
{
    public function doRecharge($vendor_id, $mobile_number, $amount, $type);

    public function topUpTransaction(TopUpTransaction $transaction);

    public function refund($amount, $log);

    public function calculateCommission($amount, TopUpVendor $topup_vendor);

    public function getCommission();
}