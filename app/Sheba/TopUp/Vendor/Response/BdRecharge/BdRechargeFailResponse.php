<?php namespace Sheba\TopUp\Vendor\Response\BdRecharge;

use App\Models\TopUpOrder;
use Sheba\TopUp\Gateway\Names;
use Sheba\TopUp\Vendor\Response\TopUpFailResponse;

class BdRechargeFailResponse extends TopUpFailResponse
{
    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function getTopUpOrder(): TopUpOrder
    {
        return TopUpOrder::where('transaction_id', $this->response['tid'])
            ->where('gateway', Names::BD_RECHARGE)
            ->first();
    }

    public function getFailedTransactionDetails()
    {
        return $this->response;
    }
}