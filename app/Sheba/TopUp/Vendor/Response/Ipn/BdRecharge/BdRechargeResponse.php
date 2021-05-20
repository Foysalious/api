<?php


namespace App\Sheba\TopUp\Vendor\Response\Ipn\BdRecharge;


use App\Models\TopUpOrder;
use Sheba\TopUp\Gateway\Names;

trait BdRechargeResponse
{
    public function findTopUpOrder(): TopUpOrder
    {
        return TopUpOrder::where('transaction_id', $this->response['tid'])
            ->where('gateway', Names::BD_RECHARGE)
            ->first();
    }
}