<?php

namespace App\Repositories;


class PartnerOrderRepository
{
    private $partnerJobRepository;

    public function __construct()
    {
        $this->partnerJobRepository = new PartnerJobRepository();
    }

    public function getOrderDetails($request)
    {
        $partner_order = $this->getOrderInfo($this->loadAllRelatedRelations($request->partner_order));
        $jobs = $partner_order->jobs->whereIn('status', $this->getStatusFromRequest($request))->each(function ($job) use ($partner_order) {
            $job['partner_order'] = $partner_order;
            $job = $this->partnerJobRepository->getJobInfo($job);
            removeRelationsAndFields($job);
            array_forget($job, 'partner_order');
        })->values()->all();
        removeRelationsAndFields($partner_order);
        $partner_order['jobs'] = $jobs;
        return $partner_order;
    }

    public function getNewOrdersWithJobs($request)
    {
        $jobs = (new PartnerRepository($request->partner))->jobs(array(constants('JOB_STATUSES')['Pending'], constants('JOB_STATUSES')['Not_Responded']));
        $all_partner_orders = collect();
        $all_jobs = collect();
        foreach ($jobs->groupBy('partner_order_id') as $jobs) {
            $order = collect([
                'customer_name' => $jobs[0]->partner_order->order->delivery_name,
                'location_name' => $jobs[0]->partner_order->order->location->name,
                'created_at' => $jobs[0]->partner_order->created_at->timestamp,
                'created_at_readable' => $jobs[0]->partner_order->created_at->diffForHumans(),
                'code' => $jobs[0]->partner_order->code(),
                'id' => $jobs[0]->partner_order->id,
                'total_job' => count($jobs),
                'jobs' => $jobs->each(function ($job) use ($all_jobs) {
                    $all_jobs->push(removeRelationsAndFields($this->partnerJobRepository->getJobInfo($job)));
                })
            ]);
            $all_partner_orders->push($order);
        }
        list($field, $sort) = $this->getSortByFieldAdOrderFromRequest($request);
        list($offset, $limit) = calculatePagination($request);
        return array_slice($this->partnerOrdersSortBy($field, $sort, $all_partner_orders, $all_jobs)->toArray(), $offset, $limit);
    }

    private function getSortByFieldAdOrderFromRequest($request)
    {
        $sort = 'sortByDesc';
        $field = 'created_at';
        if ($request->has('sort')) {
            $explode = explode(':', $request->get('sort'));
            $field = $explode[0];
            if (isset($explode[1]) && $explode[1] == 'asc') {
                $sort = 'sortBy';
            }
        }
        return array($field, $sort);
    }

    private function loadAllRelatedRelations($partner_order)
    {
        return $partner_order->load(['order.location', 'jobs' => function ($q) {
            $q->info()->orderBy('schedule_date')->with(['usedMaterials' => function ($q) {
                $q->select('id', 'job_id', 'material_name', 'material_price');
            }, 'resource.profile']);
        }]);
    }

    private function getStatusFromRequest($request)
    {
        if ($request->has('status')) {
            return explode(',', $request->status);
        } elseif ($request->has('filter')) {
            return $this->resolveStatus($request->filter);
        } else {
            return constants('JOB_STATUSES');
        }
    }

    private function resolveStatus($filter)
    {
        if ($filter == 'ongoing') {
            return array(constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Schedule_Due'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Served']);
        }
    }

    public function getOrderInfo($partner_order)
    {
        $partner_order->calculate();
        $partner_order['code'] = $partner_order->code();
        $partner_order['customer_name'] = $partner_order->order->delivery_name;
        $partner_order['customer_mobile'] = $partner_order->order->delivery_mobile;
        $partner_order['address'] = $partner_order->order->delivery_address;
        $partner_order['location'] = $partner_order->order->location->name;
        $partner_order['due_amount'] = (double)$partner_order->due;
        $partner_order['discount'] = (double)$partner_order->discount;
        $partner_order['sheba_collection'] = (double)$partner_order->sheba_collection;
        $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
        $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
        $partner_order['finance_collection'] = (double)$partner_order->finance_collection;
        $partner_order['discount'] = (double)$partner_order->discount;
        $partner_order['total_jobs'] = count($partner_order->jobs);
        $partner_order['order_status'] = $partner_order->status;
        return $partner_order;
    }


    private function partnerOrdersSortBy($field, $sort, $all_partner_orders, $all_jobs)
    {
        if ($field == 'created_at') {
            $all_partner_orders = $all_partner_orders->$sort('created_at');
        } else {
            $all_jobs = $all_jobs->$sort($field);
            $final = collect();
            foreach ($all_jobs as $job) {
                $final->push($all_partner_orders->where('id', $job->partner_order_id)->first());
            }
            $all_partner_orders = $final->unique('id');
        }
        return $all_partner_orders;
    }
}