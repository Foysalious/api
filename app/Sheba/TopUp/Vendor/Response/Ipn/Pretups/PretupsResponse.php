<?php namespace Sheba\TopUp\Vendor\Response\Ipn\Pretups;


use App\Models\TopUpOrder;

trait PretupsResponse
{
    public function findTopUpOrder(): TopUpOrder
    {
        return TopUpOrder::where('id', $this->response->tran_id)->first();
    }
}