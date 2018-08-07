<?php namespace Sheba\JobService;

use App\Models\Job;
use App\Models\JobService;
use Sheba\ModificationFields;

class JobServiceActions
{
    use ModificationFields;

    public function add()
    {
        dd(5);
    }

    public function delete(JobService $job_service)
    {
        $job = $job_service->job;
        if (!$this->isDeletable($job)) return ['code' => 400, 'msg' => "You can't delete this service"];
        $old_job_service = clone $job_service;

        dd($job->jobServices);
    }

    private function isDeletable(Job $job)
    {
        return ($job->jobServices->count() > 1);
    }
}