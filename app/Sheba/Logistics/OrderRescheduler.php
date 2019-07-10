<?php namespace Sheba\Logistics;

use App\Models\Job;
use Sheba\Logistics\DTO\Order;
use Sheba\Logistics\Literals\OrderKeys;
use Sheba\Logistics\LogisticsNatures\NatureFactory;

class OrderRescheduler extends OrderHandler
{
    /** @var Order */
    private $order;
    /** @var Job */
    private $job;

    /**
     * @param Job $job
     * @return $this
     * @throws \Exception
     */
    public function setJob(Job $job)
    {
        $this->job = $job;
        $this->order = $job->getFirstLogisticOrder();
        return $this;
    }

    /**
     * @param $date
     * @param $time
     * @throws Exceptions\LogisticServerError
     */
    public function reschedule($date, $time)
    {
        $logistic_nature = NatureFactory::getLogisticNature($this->job, OrderKeys::FIRST);
        $pick_up_time = $logistic_nature->getPickupTimeFromDateTime($date, $time);
        $this->repo->reschedule($this->order, $pick_up_time->toDateString(), $pick_up_time->toTimeString());
    }
}