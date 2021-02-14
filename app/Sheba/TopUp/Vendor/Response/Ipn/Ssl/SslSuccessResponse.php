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
        $date = Carbon::now()->toDateString();
        return TopUpOrder::where('transaction_id', $this->response['guid'])
            ->where('gateway', Gateway::SSL)
            ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->first();
    }
}
