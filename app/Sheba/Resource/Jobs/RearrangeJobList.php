<?php namespace Sheba\Resource\Jobs;

use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Sheba\Jobs\JobStatuses;

class RearrangeJobList
{
    /**
     *  Served,Process and then Others
     * @param Collection $jobs
     * @return Collection
     */
    public function rearrange(Collection $jobs)
    {
        return $this->sortJobs($jobs);
    }

    private function sortJobs(Collection $jobs)
    {
        return $jobs->map(function ($job) {
            $job['schedule_time_start_timestamp'] = $this->makeScheduleStartTimestamp($job);
            return $job;
        })->sortBy('schedule_time_start_timestamp');
    }

    private function makeScheduleStartTimestamp(Job $job)
    {
        return Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_start)->timestamp;
    }
}