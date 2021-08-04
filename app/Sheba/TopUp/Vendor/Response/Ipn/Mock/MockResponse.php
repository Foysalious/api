<?php namespace Sheba\TopUp\Vendor\Response\Ipn\Mock;


use App\Models\TopUpOrder;

trait MockResponse
{
    public function findTopUpOrder(): TopUpOrder
    {
        return $this->topUpOrder;
    }
}