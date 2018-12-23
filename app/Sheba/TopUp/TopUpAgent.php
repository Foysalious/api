<?php namespace Sheba\TopUp;

use App\Models\TopUpOrder;
use App\Models\TopUpVendor;

interface TopUpAgent
{
    public function doRecharge($vendor_id, $mobile_number, $amount, $type);

    public function topUpTransaction($amount, $log, TopUpOrder $top_up_order);

    public function refund($amount, $log);

    public function calculateCommission($amount, TopUpVendor $topup_vendor);

    public function getCommission();
}