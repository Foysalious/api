<?php

namespace Sheba\TopUp\Vendor\Internal;

use App\Models\TopUpVendor;
use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

trait Ssl
{
    private $ssl;

    public function __construct(SslClient $ssl)
    {
        $this->ssl = $ssl;
    }

    /**
     * @param TopUpRequest $top_up_request
     * @return TopUpResponse
     * @throws \SoapFault
     */
    public function recharge(TopUpRequest $top_up_request): TopUpResponse
    {
        return $this->ssl->recharge($top_up_request);
    }

    public function getTopUpInitialStatus()
    {
        return config('topup.status.pending');
    }

    /**
     * @param $amount
     * @throws \Exception
     */
    public function deductAmount($amount)
    {
        TopUpVendor::whereIn('id', [4, 5, 6])->update(['amount' => $this->model->amount - $amount]);
    }

    public function refill($amount)
    {
        TopUpVendor::whereIn('id', [4, 5, 6])->increment('amount', $amount);
        // $this->createNewRechargeHistory($amount, 4);
        // $this->createNewRechargeHistory($amount, 5);
        // $this->createNewRechargeHistory($amount, 6);
    }
}