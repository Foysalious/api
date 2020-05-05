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
        $process_jobs = $this->sortJobs($jobs->whereIn('status', [JobStatuses::PROCESS, JobStatuses::SERVE_DUE]));
        $served_jobs = $this->sortJobs($jobs->where('status', JobStatuses::SERVED));
        $other_jobs = $jobs->filter(function ($job) {
            return !in_array($job->status, [JobStatuses::PROCESS, JobStatuses::SERVED, JobStatuses::SERVE_DUE]);
        });
        $other_jobs = $this->sortJobs($other_jobs);
        return $served_jobs->merge($process_jobs->merge($other_jobs));
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