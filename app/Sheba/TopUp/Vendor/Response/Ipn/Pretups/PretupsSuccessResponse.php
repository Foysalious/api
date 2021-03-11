<?php namespace Sheba\TopUp\Vendor\Response\Ipn\Pretups;

use App\Models\TopUpOrder;
use Sheba\TopUp\Vendor\Response\Ipn\SuccessResponse;

class PretupsSuccessResponse extends SuccessResponse
{
    public function getTopUpOrder(): TopUpOrder
    {
        return TopUpOrder::where('id', $this->response->tran_id)->first();
    }
}
