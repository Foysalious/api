<?php namespace Sheba\Logistics\Listeners;

use Sheba\Dal\JobService\Events\JobServiceSaved;
use Sheba\Dal\JobService\JobService;
use Sheba\Logistics\Exceptions\LogisticServerError;

class JobServiceListener extends BaseListener
{
    /**
     * @param JobServiceSaved $event
     * @throws LogisticServerError
     */
    public function handle(JobServiceSaved $event)
    {
        /** @var JobService $job_service */
        $job_service = $event->model;
        $this->update($job_service->job->partnerOrder);
    }
}
