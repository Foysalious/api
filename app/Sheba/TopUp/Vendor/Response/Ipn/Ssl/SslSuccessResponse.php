<?php namespace Sheba\TopUp\Vendor\Response\Ipn\Ssl;

use App\Models\TopUpOrder;
use Carbon\Carbon;
use Sheba\Dal\TopupVendor\Gateway;
use Sheba\TopUp\Vendor\Response\Ipn\SuccessResponse;

class SslSuccessResponse extends SuccessResponse
{
    public function getTopUpOrder(): TopUpOrder
    {
        $guid = is_array($this->response) ? $this->response['guid'] : $this->response->guid;
        return TopUpOrder::where('transaction_id', $guid)
            ->where('gateway', Gateway::SSL)
            ->first();
    }
}
