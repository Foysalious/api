<?php namespace Sheba\Jobs;

use App\Models\Job;
use Sheba\Helpers\ConstGetter;

class DeliveryStatuses
{
    use ConstGetter;

    const SHEBA_DELIVERY = "Sheba Delivery";
    const DELIVERY_SCHEDULED = "Delivery Scheduled";
    const RIDER_ASSIGNED = "Rider Assigned";
    const SEARCHING_RIDER = "Searching Rider";

    /** @var Job $job */
    private $job;

    public function setJob(Job $job)
    {
        $this->job = $job;
        return $this;
    }

    public function getApplicable()
    {
        if ($this->isShebaDeliverable() && !$this->isDeliveryScheduled()) return self::SHEBA_DELIVERY;
        if ($this->isDeliveryScheduled()) return self::DELIVERY_SCHEDULED;
        return null;
    }

    public function isShebaDeliverable()
    {
        return $this->job->needsLogistic();
    }

    public function isDeliveryScheduled()
    {
        return true;
    }
}