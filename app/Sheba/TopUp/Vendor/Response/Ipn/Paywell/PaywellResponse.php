<?php namespace Sheba\TopUp\Vendor\Response\Ipn\Paywell;


use App\Models\TopUpOrder;

trait PaywellResponse
{
    public function findTopUpOrder(): TopUpOrder
    {
        return TopUpOrder::where('id', $this->response->tran_id)->first();
    }
}