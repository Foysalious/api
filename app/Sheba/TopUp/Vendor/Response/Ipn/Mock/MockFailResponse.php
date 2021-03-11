<?php namespace Sheba\TopUp\Vendor\Response\Ipn\Mock;

use App\Models\TopUpOrder;
use Sheba\TopUp\Vendor\Response\Ipn\FailResponse;

class MockFailResponse extends FailResponse
{
    public function getTopUpOrder(): TopUpOrder
    {
        return TopUpOrder::where('id', $this->response->tran_id)->first();
    }
}
