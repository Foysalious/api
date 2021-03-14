<?php namespace Sheba\TopUp;


use Exception;
use Sheba\Dal\TopupOrder\FailedReason;
use Sheba\TopUp\Vendor\Response\Ipn\FailResponse;
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
     * @param $action
     * @throws Exception
     */
    private function doTransaction($action)
    {
        try {
            DB::transaction($action);
        } catch (Exception $e) {
            $this->markOrderAsSystemError($e);
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function reload()
    {
        if ($this->topUpOrder->isFailed() || $this->topUpOrder->isSuccess()) return;
        $vendor = $this->getVendor();
        $response = $vendor->enquire($this->topUpOrder);
        $response->handleTopUp();
    }
}
