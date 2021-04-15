<?php namespace Sheba\TopUp\Vendor\Response\BdRecharge;

use App\Models\TopUpOrder;
use Sheba\TopUp\Gateway\Names;
use Sheba\TopUp\Vendor\Response\Ipn\SuccessResponse;

class BdRechargeSuccessResponse extends SuccessResponse
{

    public function getSuccessfulTransactionDetails()
    {
        return $this->response;
    }

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

    protected function findTopUpOrder(): TopUpOrder
    {
        // TODO: Implement findTopUpOrder() method.
    }
}