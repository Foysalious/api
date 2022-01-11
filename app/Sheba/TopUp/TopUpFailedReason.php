<?php namespace Sheba\TopUp;

use App\Models\TopUpOrder;
use Sheba\TopUp\Gateway\GatewayFactory;
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
            $topup_failed_reason = GatewayFactory::getByOrder($this->topUpOrder)->getFailedReason();
            return $topup_failed_reason->setTransaction($this->topUpOrder->transaction_details)->getReason();
        } catch (Throwable $e) {
            logError($e);
            return null;
        }
    }
}