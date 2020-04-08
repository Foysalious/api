<?php namespace Sheba\Logistics;


use App\Models\Job;
use App\Models\PartnerOrder;
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
     * @throws \Exception
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
        if ((double)$logistic_order->collectableAmount == (double)$this->partnerOrder->due) return;
        $logistic_order_manager->updateVendorCollectable($logistic_order, $this->partnerOrder->due);
    }


}