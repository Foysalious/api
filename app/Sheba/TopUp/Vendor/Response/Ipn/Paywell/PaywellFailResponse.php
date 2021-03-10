<?php namespace Sheba\TopUp\Vendor\Response\Ipn\Paywell;

use App\Models\TopUpOrder;
use Sheba\TopUp\Vendor\Response\Ipn\FailResponse;

class PaywellFailResponse extends FailResponse
{
    public function getTopUpOrder(): TopUpOrder
    {
        return TopUpOrder::where('id', $this->response->tran_id)->first();
    }
}
