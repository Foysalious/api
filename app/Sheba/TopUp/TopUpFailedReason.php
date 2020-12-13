<?php namespace Sheba\TopUp;

use App\Models\TopUpOrder;
use Sheba\TopUp\FailedReason\FailedReasonFactory;
use Throwable;

class TopUpFailedReason
{
    /** @var TopUpOrder $topUpOrder */
    private $topUpOrder;

    public function setTopup(TopUpOrder $top_up_order)
    {
        $this->topUpOrder = $top_up_order;
        return $this;
    }

    public function getFailedReason()
    {
        if (!$this->topUpOrder->isFailed()) return null;
        if (!$this->topUpOrder->transaction_details) return null;
        try {
            $topup_failed_reason = FailedReasonFactory::make($this->topUpOrder);
            return $topup_failed_reason->setTransaction($this->topUpOrder->transaction_details)->getReason();
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }
}