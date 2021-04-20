<?php namespace Sheba\TopUp;


use Sheba\Dal\TopupOrder\FailedReason;
use Sheba\TopUp\Exception\PaywellTopUpStillNotResolved;
use Sheba\TopUp\Vendor\Response\Ipn\FailResponse;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\Ipn\SuccessResponse;
use DB;

class TopUpLifecycleManager extends TopUpManager
{
    /**
     * @param FailResponse $fail_response
     * @throws \Throwable
     */
    public function fail(FailResponse $fail_response)
    {
        if ($this->topUpOrder->isFailed()) return;

        $this->doTransaction(function () use ($fail_response) {
            $this->statusChanger->failed(FailedReason::GATEWAY_ERROR, $fail_response->getTransactionDetailsString());
            if ($this->topUpOrder->isAgentDebited()) $this->refund();
            $this->getVendor()->refill($this->topUpOrder->amount);
        });
    }

    /**
     * @param SuccessResponse $success_response
     * @throws \Throwable
     */
    public function success(SuccessResponse $success_response)
    {
        if ($this->topUpOrder->isSuccess()) return;

        $this->doTransaction(function () use ($success_response) {
            $this->statusChanger->successful($success_response->getTransactionDetailsString());
        });
    }

    /**
     * @return IpnResponse | void
     * @throws PaywellTopUpStillNotResolved | \Throwable
     */
    public function reload()
    {
        if (!$this->topUpOrder->canRefresh()) return;
        $vendor = $this->getVendor();
        $response = $vendor->enquire($this->topUpOrder);
        $response->handleTopUp();
        return $response;
    }
}
