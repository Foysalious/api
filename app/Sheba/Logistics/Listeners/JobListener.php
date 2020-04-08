<?php namespace Sheba\Logistics\Listeners;

use App\Models\Job;
use Sheba\Dal\Job\Events\JobSaved;
use Sheba\Logistics\UpdatePriceHandler;
use Sheba\Report\Listeners\BaseSavedListener;

class JobListener extends BaseSavedListener
{
    private $priceHandler;

    public function __construct(UpdatePriceHandler $priceHandler)
    {
        $this->priceHandler = $priceHandler;
    }

    public function handle(JobSaved $event)
    {
        /** @var Job $job */
        $job = $event->model;
        $this->priceHandler->setPartnerOrder($job->partnerOrder)->update();
    }
}