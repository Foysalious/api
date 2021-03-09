<?php namespace Sheba\TopUp\Vendor\Response\Ipn\Ssl;

use App\Models\TopUpOrder;
use Sheba\TopUp\Vendor\Response\Ipn\FailResponse;

class SslFailResponse extends FailResponse
{
    public function getTopUpOrder(): TopUpOrder
    {
        return TopUpOrder::where('transaction_details', 'like', '%' . $this->response['vr_guid'] . '%')->first();
    }
}
