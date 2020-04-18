<?php namespace Sheba\Resource\Jobs\Service;


use Sheba\Dal\JobService\JobService;

class Updater
{
    /** @var JobService */
    private $jobService;
    /** @var float */
    private $quantity;

    /**
     * @param JobService $jobService
     * @return Updater
     */
    public function setJobService($jobService)
    {
        $this->jobService = $jobService;
        return $this;
    }

    /**
     * @param float $quantity
     * @return Updater
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }

}