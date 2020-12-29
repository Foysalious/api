<?php namespace Sheba\TopUp\Vendor\Response\Paywell;

use App\Models\TopUpOrder;
use Sheba\TopUp\Vendor\Response\TopUpFailResponse;

class PaywellFailResponse extends TopUpFailResponse
{
    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function getTopUpOrder(): TopUpOrder
    {
        return TopUpOrder::where('id', $this->response->tran_id)->first();
    }

    public function getFailedTransactionDetails()
    {
        return $this->response;
    }
}