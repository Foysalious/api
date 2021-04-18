<?php namespace Sheba\TopUp\Vendor\Response\Ipn\Ssl;

use App\Models\TopUpOrder;
use Carbon\Carbon;
use Sheba\Dal\TopupVendor\Gateway;
use Sheba\TopUp\Vendor\Response\Ipn\SuccessResponse;

class SslSuccessResponse extends SuccessResponse
{

    public function getSuccessfulTransactionDetails()
    {
        return $this->response;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    public function getTopUpOrder(): TopUpOrder
    {
        $today = Carbon::today()->endOfDay()->toDateTimeString();
        $yesterday = Carbon::yesterday()->subDays(3)->startOfDay()->toDateTimeString();

        return TopUpOrder::where('transaction_id', $this->response['guid'])
            ->where('gateway', Gateway::SSL)
            ->whereBetween('created_at', [$yesterday, $today])
            ->first();
    }
}
