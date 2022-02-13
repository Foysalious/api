<?php namespace Sheba\TopUp\Vendor\Response\Ipn\PayStation;

use App\Models\TopUpOrder;
use Sheba\TopUp\Gateway\Names;

trait PayStationEnquiryResponse
{
    public function findTopUpOrder(): TopUpOrder
    {
        return TopUpOrder::gateway(Names::PAY_STATION)
            ->where('id', TopUpOrder::getIdFromUniformGatewayRefId($this->response['your_ref']))
            ->first();
    }
}