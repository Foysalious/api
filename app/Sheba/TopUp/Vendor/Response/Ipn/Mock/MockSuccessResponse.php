<?php namespace Sheba\TopUp\Vendor\Response\Ipn\Mock;

use App\Models\TopUpOrder;
use Sheba\TopUp\Vendor\Response\Ipn\SuccessResponse;

class MockSuccessResponse extends SuccessResponse
{
    public function getTopUpOrder(): TopUpOrder
    {
        return TopUpOrder::where('id', $this->response->tran_id)->first();
    }
}
