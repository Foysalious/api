<?php namespace Sheba\Logistics\Listeners;


use Sheba\Dal\JobService\Events\JobServiceSaved;
use Sheba\Dal\JobService\JobService;
use Sheba\Logistics\UpdatePriceHandler;
use Sheba\Report\Listeners\BaseSavedListener;

class JobServiceListener extends BaseSavedListener
{
    private $priceHandler;

    public function __construct(UpdatePriceHandler $priceHandler)
    {
        $this->priceHandler = $priceHandler;
    }

    public function handle(JobServiceSaved $event)
    {
        /** @var JobService $job_service */
        $job_service = $event->model;
        $this->priceHandler->setPartnerOrder($job_service->job->partnerOrder)->update();
    }
}