<?php

namespace Sheba\TopUp\Vendor\Response\Ssl;

use App\Models\TopUpOrder;
use Sheba\TopUp\Vendor\Response\TopUpFailResponse;

class SslFailResponse extends TopUpFailResponse
{

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function getTopUpOrder(): TopUpOrder
    {
        return TopUpOrder::where('transaction_details', 'like', '%' . $this->response['vr_guid'] . '%')->first();
    }
}