<?php namespace Sheba\TopUp;


use Exception;
use Sheba\Dal\TopupOrder\FailedReason;
use Sheba\TopUp\Exception\PaywellTopUpStillNotResolved;
use Sheba\TopUp\Vendor\Response\Ipn\FailResponse;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\Ipn\SuccessResponse;
use Sheba\TopUp\Vendor\VendorFactory;
use DB;

class TopUpLifecycleManager extends TopUpManager
{
    /**
     * @param FailResponse $fail_response
     * @throws Exception
     */
    public function fail(FailResponse $fail_response)
    {
        if ($this->topUpOrder->isFailed()) return;

        $this->doTransaction(function () use ($fail_response) {
            $this->statusChanger->failed(FailedReason::GATEWAY_ERROR, $fail_response->getTransactionDetailsString());
            $this->refund();
            $this->getVendor()->refill($this->topUpOrder->amount);
        });
    }

    /**
     * @param SuccessResponse $success_response
     * @throws Exception
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
     * @throws Exception | PaywellTopUpStillNotResolved
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
