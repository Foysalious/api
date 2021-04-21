<?php namespace Sheba\TopUp\Vendor\Response\Ipn\Ssl;


use App\Models\TopUpOrder;
use Carbon\Carbon;
use Sheba\Dal\TopupVendor\Gateway;

trait SslResponse
{
    public function findTopUpOrder(): TopUpOrder
    {
        $response = json_decode(json_encode($this->response), 1);

        $order = $this->runQuery(TopUpOrder::where('transaction_id', $response['guid']));

        if ($order) return $order;

        return $this->runQuery(TopUpOrder::where('transaction_details', 'like', '%' . $response['vr_guid'] . '%'));
    }

    private function runQuery($base_query)
    {
        $today = Carbon::today()->endOfDay()->toDateTimeString();
        $yesterday = Carbon::yesterday()->startOfDay()->toDateTimeString();

        return $base_query
            ->where('gateway', Gateway::SSL)
            ->whereBetween('created_at', [$yesterday, $today])
            ->first();
    }
}
