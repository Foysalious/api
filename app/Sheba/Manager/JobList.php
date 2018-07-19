<?php

namespace Sheba\Manager;


use App\Models\Partner;

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
                if ($job->cancelRequests->where('status', 'Pending')->count() > 0) continue;
                $jobs->push($job);
            }
        }
        return $jobs;
    }

    private function loadAllJobs($filter)
    {
        $this->partner->load(['partnerOrders' => function ($q) use ($filter) {
            $q->$filter()->with(['jobs' => function ($q) use ($filter) {
                $q->$filter()->orderBy('id', 'desc')->with('cancelRequests');
            }]);
        }]);
    }

}