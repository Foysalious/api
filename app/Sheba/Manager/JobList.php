<?php

namespace Sheba\Manager;


use App\Models\Partner;
use Carbon\Carbon;

class JobList
{
    private $partner;

    public function __construct($partner)
    {
        $this->partner = ($partner) instanceof Partner ? $partner : Partner::find($partner);
    }

    public function ongoingJobs()
    {
        $this->loadAllJobs('ongoing');
        $jobs = collect();
        foreach ($this->partner->partnerOrders as $partnerOrder) {
            foreach ($partnerOrder->jobs as $job) {
                $job['schedule_timestamp'] = $partnerOrder->getVersion() == 'v2' ? Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_start)->timestamp : Carbon::parse($job->schedule_date)->timestamp;
                $jobs->push($job);
            }
        }
        return $this->onGoingJobsFilter($jobs);
    }

    private function loadAllJobs($filter)
    {
        $this->partner->load(['partnerOrders' => function ($q) use ($filter) {
            $q->$filter()->with(['jobs' => function ($q) use ($filter) {
                $q->$filter()->orderBy('id', 'desc')->with('cancelRequests');
            }]);
        }]);
    }

    private function onGoingJobsFilter($jobs)
    {
        $jobs_without_resource = collect();
        $final_jobs = collect();
        foreach ($jobs as $job) {
            if ($job->cancelRequests->count() > 0) {
                if ($job->cancelRequests->where('status', 'Pending')->count() > 0) continue;
                $job['is_cancel_request_rejected'] = 0;
                if ($job->cancelRequests->last()->status == constants('CANCEL_REQUEST_STATUSES')['Disapproved']) $job['is_cancel_request_rejected'] = 1;
            }
            if ($job->resource_id == null) {
                $jobs_without_resource->push($job);
                continue;
            }
            $final_jobs->push($job);
        }
        $group_by_jobs = $final_jobs->groupBy('schedule_date')->sortBy(function ($item, $key) {
            return $key;
        });
        $final = collect();
        foreach ($group_by_jobs as $key => $jobs) {
            $jobs = $jobs->sortBy('schedule_timestamp');
            foreach ($jobs as $job) {
                $final->push($job);
            }
        }
        return $jobs_without_resource->merge($final);
    }

}