<?php


namespace Sheba\Resource\Jobs;


use App\Models\Job;
use App\Models\Resource;
use Carbon\Carbon;
use Sheba\Dal\Job\JobRepositoryInterface;

class JobChecker
{
    private $jobRepository;
    private $resource;

    public function __construct(JobRepositoryInterface $job_repository)
    {
        $this->jobRepository = $job_repository;
    }

    /**
     * @param Resource $resource
     * @return $this
     */public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function checkIfJobIsOngoing(Job $job)
    {
        $jobs = $this->jobRepository->getOngoingJobsForResource($this->resource->id)->tillNow()->get();
        return !!($jobs->filter(function($item) use ($job) {
            return $item->id == $job->id;
        })->first());
    }

    public function checkIfJobIsInCurrentSlot(Job $job)
    {
        $start_time = Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_start);
        $end_time = Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_end);

        return Carbon::now()->between($start_time, $end_time);
    }

    public function checkIfFromOldSlot(Job $job)
    {
        $end_time = Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_end);
        return Carbon::now() > $end_time;
    }

    public function checkIfReadyForAction(Job $job)
    {
        return $this->checkIfJobIsOngoing($job)
            && ($this->checkIfJobIsInCurrentSlot($job)
            || $this->checkIfFromOldSlot($job));
    }
}