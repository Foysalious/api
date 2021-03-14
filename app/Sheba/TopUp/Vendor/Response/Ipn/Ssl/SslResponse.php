<?php namespace Sheba\TopUp\Vendor\Response\Ipn\Ssl;


use App\Models\TopUpOrder;
use Sheba\Dal\TopupVendor\Gateway;

trait SslResponse
{
    public function findTopUpOrder(): TopUpOrder
    {
        $response = json_decode(json_encode($this->response), 1);

        $order = TopUpOrder::where('transaction_id', $response['guid'])
            ->where('gateway', Gateway::SSL)
            ->first();

        if ($order) return $order;

        return TopUpOrder::where('transaction_details', 'like', '%' . $response['vr_guid'] . '%')
            ->where('gateway', Gateway::SSL)
            ->first();
    }
}