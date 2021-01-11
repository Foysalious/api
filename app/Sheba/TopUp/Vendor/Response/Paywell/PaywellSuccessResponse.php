<?php namespace Sheba\TopUp\Vendor\Response\Paywell;

use App\Models\TopUpOrder;
use Sheba\TopUp\Vendor\Response\Ipn\SuccessResponse;

class PaywellSuccessResponse extends SuccessResponse
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
        return TopUpOrder::where('id', $this->response->tran_id)->first();
    }
}