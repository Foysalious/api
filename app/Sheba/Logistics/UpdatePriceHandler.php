<?php namespace Sheba\Logistics;

use App\Models\Job;
use App\Models\PartnerOrder;
use Exception;
use Sheba\Dal\JobService\JobService;
use Sheba\Logistics\OrderManager as LogisticOrderManager;

class UpdatePriceHandler
{
    /** @var PartnerOrder */
    private $partnerOrder;

    /**
     * @param PartnerOrder $partnerOrder
     * @return UpdatePriceHandler
     */
    public function setPartnerOrder($partnerOrder)
    {
        $this->partnerOrder = $partnerOrder;
        return $this;
    }

    /**
     * @throws Exceptions\LogisticServerError
     * @throws Exception
     */
    public function update()
    {
        /** @var Job $job */
        $job = $this->partnerOrder->lastJob();
        $logistic_order = $job->getCurrentLogisticOrder();
        if (!$logistic_order) return;

        /** @var LogisticOrderManager $logistic_order_manager */
        $logistic_order_manager = app(LogisticOrderManager::class);
        $logistic_order_manager->setJob($job);
        $this->partnerOrder->calculate(1);
        $vendor_collectable = $this->partnerOrder->due > 0 ? $this->partnerOrder->due : 0;
        if ($logistic_order->getVendorCollectableAmount() != $vendor_collectable) $logistic_order_manager->updateVendorCollectable($logistic_order, $vendor_collectable);
        if ($this->partnerOrder->due < 0 && $job->logisticDue > 0) {
            $has_enough_collection = $this->partnerOrder->due + $job->logisticDue <= 0;
            $logistic_order_manager->pay($logistic_order, $has_enough_collection ? $job->logisticDue : $this->partnerOrder->due * -1);
        }
    }


}
