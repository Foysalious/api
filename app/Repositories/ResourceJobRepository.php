<?php

namespace App\Repositories;


class ResourceJobRepository
{

    public function rearrange($jobs)
    {
        $process_job = $jobs->where('status', 'Process')->values()->all();
        $other_jobs = $jobs->filter(function ($job) {
            return $job->status != 'Process';
        });
        $other_jobs = $other_jobs->map(function ($item) {
            return array_add($item, 'preferred_time_priority', constants('JOB_PREFERRED_TIMES_PRIORITY')[$item->preferred_time]);
        });
        $other_jobs = $other_jobs->sortBy(function ($job) {
            return sprintf('%-12s%s', $job->schedule_date, $job->preferred_time_priority);
        })->values()->all();
        $jobs = array_merge($process_job, $other_jobs);
        return $jobs;
    }
}