<?php namespace Sheba\TopUp\Vendor\Response\Ipn\PayStation;

use App\Models\TopUpOrder;
use Sheba\TopUp\Gateway\Names;

trait PayStationIpnResponse
{
    public function findTopUpOrder(): TopUpOrder
    {
        return TopUpOrder::where('transaction_id', $this->response['Reference'])
            ->where('gateway', Names::PAY_STATION)
            ->first();
    }
}