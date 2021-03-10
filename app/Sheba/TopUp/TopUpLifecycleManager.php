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

        try {
            DB::transaction(function () use ($fail_response) {
                $this->statusChanger->failed(FailedReason::GATEWAY_ERROR, $fail_response->getTransactionDetailsString());
                $this->refund();
                $vendor = (new VendorFactory())->getById($this->topUpOrder->vendor_id);
                $vendor->refill($this->topUpOrder->amount);
            });
        } catch (Exception $e) {
            $this->markOrderAsSystemError($e);
            throw $e;
        }
    }

    /**
     * @param SuccessResponse $success_response
     * @throws Exception
     */
    public function success(SuccessResponse $success_response)
    {
        if ($this->topUpOrder->isSuccess()) return;

        try {
            DB::transaction(function () use ($success_response) {
                $this->statusChanger->successful(json_encode($success_response->getTransactionDetails()));
            });
        } catch (Exception $e) {
            $this->markOrderAsSystemError($e);
            throw $e;
        }
    }
}
