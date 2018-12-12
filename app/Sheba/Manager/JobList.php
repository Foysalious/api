<?php namespace Sheba\Manager;

use App\Models\Partner;
use Carbon\Carbon;

class JobList
{
    private $partner;

    public function __construct($partner)
    {
        $this->partner = ($partner) instanceof Partner ? $partner : Partner::find($partner);
    }

    public function ongoing()
    {
        $this->loadAllJobs('ongoing');
        $jobs = collect();
        foreach ($this->partner->partnerOrders as $partnerOrder) {
            foreach ($partnerOrder->jobs as $job) {
                $jobs->push($job);
            }
        }
        return $this->onGoingFilter($jobs);
    }

    private function loadAllJobs($filter)
    {
        $this->partner->load(['partnerOrders' => function ($q) use ($filter) {
            $q->$filter()->with(['jobs' => function ($q) use ($filter) {
                $q->$filter()->orderBy('id', 'desc')->with('cancelRequests');
            }]);
        }]);
    }

    private function onGoingFilter($jobs)
    {
        $final_jobs = collect();
        $jobs_without_resource = collect();
        $jobs_with_resource = collect();
        foreach ($jobs as $job) {
            $job['schedule_timestamp'] = $job->preferred_time_start != null ? Carbon::parse($job->schedule_date . ' ' . $job->preferred_time_start)->timestamp : Carbon::parse($job->schedule_date)->timestamp;
            $job['is_cancel_request_rejected'] = 0;
            if ($job->cancelRequests->count() > 0) {
                if ($job->cancelRequests->where('status', 'Pending')->count() > 0) continue;
                if ($job->cancelRequests->last()->status == constants('CANCEL_REQUEST_STATUSES')['Disapproved']) $job['is_cancel_request_rejected'] = 1;
            }
            if ($job->resource_id == null) $jobs_without_resource->push($job);
            else $jobs_with_resource->push($job);
        }
        $group_by_jobs = $jobs_with_resource->groupBy('schedule_date')->sortBy(function ($item, $key) {
            return $key;
        });
        foreach ($group_by_jobs as $key => $jobs) {
            $jobs = $jobs->sortBy('schedule_timestamp');
            foreach ($jobs as $job) {
                $final_jobs->push($job);
            }
        }
        return $jobs_without_resource->merge($final_jobs);
    }
}