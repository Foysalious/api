<?php namespace Sheba\TopUp\Vendor\Response\Ipn\Ssl;

use App\Models\TopUpOrder;
use Sheba\TopUp\Vendor\Response\Ipn\SuccessResponse;

class SslSuccessResponse extends SuccessResponse
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
        return TopUpOrder::where('transaction_details', 'like', '%' . $this->response['vr_guid'] . '%')->first();
    }
}