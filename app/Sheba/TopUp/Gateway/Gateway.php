<?php namespace Sheba\TopUp\Gateway;

use App\Models\TopUpOrder;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

interface Gateway
{
    public function recharge(TopUpOrder $topup_order): TopUpResponse;

    public function getInitialStatus();

    public function getShebaCommission();

}
