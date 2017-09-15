<?php

namespace App\Repositories;


class ResourceJobRepository
{

    public function rearrange($jobs)
    {
        $process_job = $jobs->where('status', 'Process')->values()->all();
        $served_jobs = $this->_getLastServedJobOfPartnerOrder($jobs->where('status', 'Served')->values()->all());
        $other_jobs = $jobs->filter(function ($job) {
            return $job->status != 'Process' && $job->status != 'Served';
        });
        $other_jobs = $other_jobs->map(function ($item) {
            return array_add($item, 'preferred_time_priority', constants('JOB_PREFERRED_TIMES_PRIORITY')[$item->preferred_time]);
        });
        $other_jobs = $other_jobs->sortBy(function ($job) {
            return sprintf('%-12s%s', $job->schedule_date, $job->preferred_time_priority);
        })->values()->all();
        $jobs = array_merge($process_job, $other_jobs);
        $jobs = array_merge($served_jobs, $jobs);
        return $jobs;
    }

    public function getJobs($resource)
    {
        $resource->load(['jobs' => function ($q) {
            $q->select('id', 'resource_id', 'schedule_date', 'preferred_time', 'service_name', 'status', 'partner_order_id', 'service_unit_price')
                ->where('schedule_date', '<=', date('Y-m-d'))->whereIn('status', ['Accepted', 'Served', 'Process', 'Schedule Due'])
                ->with(['partner_order' => function ($q) {
                    $q->with('order.customer.profile');
                }]);
        }]);
        return $resource->jobs;
    }

    private function _getLastServedJobOfPartnerOrder($jobs)
    {
        $final = [];
        foreach ($jobs as $job) {
            $partner_order_jobs = $job->partner_order->jobs->map(function ($item) {
                return array_add($item, 'preferred_time_priority', constants('JOB_PREFERRED_TIMES_PRIORITY')[$item->preferred_time]);
            });
            $last_job = $partner_order_jobs->sortBy(function ($job) {
                return sprintf('%-12s%s', $job->schedule_date, $job->preferred_time_priority);
            })->last();
            $partner_order = $job->partner_order;
            $partner_order->calculate();
            if ($last_job->id == $job->id && $partner_order->due != 0) {
                array_push($final, $job);
            }
        }
        return $final;
    }
}