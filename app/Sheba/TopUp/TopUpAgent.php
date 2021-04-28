<?php namespace Sheba\TopUp;

use App\Models\TopUpVendor;

interface TopUpAgent
{
    public function topUpTransaction(TopUpTransaction $transaction);

    public function refund($amount, $log);

    public function calculateCommission($amount, TopUpVendor $topup_vendor);

    /**
     * @return TopUpCommission
     */
    public function getCommission();

    public function getMobile();

    public function reload();
}
