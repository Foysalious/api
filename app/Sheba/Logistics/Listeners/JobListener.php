<?php namespace Sheba\Logistics\Listeners;

use App\Models\Job;
use Sheba\Dal\Job\Events\JobSaved;
use Sheba\Logistics\Exceptions\LogisticServerError;

class JobListener extends BaseListener
{
    /**
     * @param JobSaved $event
     * @throws LogisticServerError
     */
    public function handle(JobSaved $event)
    {
        /** @var Job $job */
        $job = $event->model;
        $this->update($job->partnerOrder);
    }
}
