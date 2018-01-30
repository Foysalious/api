<?php

namespace App\Repositories;

use App\Models\PartnerOrder;
use Carbon\Carbon;

class PartnerOrderRepository
{
    private $partnerJobRepository;

    public function __construct()
    {
        $this->partnerJobRepository = new PartnerJobRepository();
    }

    public function getOrderDetails($request)
    {
        $partner_order = $this->getInfo($this->loadAllRelatedRelations($request->partner_order));
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

    public function getOrderDetailsV2($request)
    {
        $partner_order = $this->getInfo($this->loadAllRelatedRelationsV2($request->partner_order));
        $jobs = $partner_order->jobs->each(function ($job) use ($partner_order) {
            $job['partner_order'] = $partner_order;
            $job = $this->partnerJobRepository->getJobInfo($job);
            $services = [];
            $job->jobServices->each(function ($job_service) use (&$services) {
                array_push($services, $this->partnerJobRepository->getJobServiceInfo($job_service));
            });
            removeRelationsAndFields($job);
            $job['services'] = $services;
            array_forget($job, 'partner_order');
        })->values()->all();
        removeRelationsAndFields($partner_order);
        $partner_order['jobs'] = $jobs;
        return $partner_order;
    }

    public function getNewOrdersWithJobs($request)
    {
        list($offset, $limit) = calculatePagination($request);
        $jobs = (new PartnerRepository($request->partner))->jobs(array(constants('JOB_STATUSES')['Pending'], constants('JOB_STATUSES')['Not_Responded']), $offset, $limit);
        $all_partner_orders = collect();
        $all_jobs = collect();
        foreach ($jobs->groupBy('partner_order_id') as $jobs) {
            $jobs[0]->partner_order->calculate(true);
            $order = collect([
                'customer_name' => $jobs[0]->partner_order->order->delivery_name,
                'location_name' => $jobs[0]->partner_order->order->location->name,
                'created_at' => $jobs[0]->partner_order->created_at->timestamp,
                'created_at_readable' => $jobs[0]->partner_order->created_at->diffForHumans(),
                'code' => $jobs[0]->partner_order->code(),
                'id' => $jobs[0]->partner_order->id,
                'total_price' => (double)$jobs[0]->partner_order->totalPrice,
                'category_name' => $jobs[0]->category->name,
                'job_id' => $jobs[0]->id,
                'schedule_date' => $jobs[0]->schedule_date,
                'preferred_time' => $jobs[0]->preferred_time
            ]);
            $all_partner_orders->push($order);
        }
        list($field, $orderBy) = $this->getSortByFieldAdOrderFromRequest($request);
        $orderBy = $orderBy == 'asc' ? 'sortBy' : 'sortByDesc';
        list($offset, $limit) = calculatePagination($request);
        return array_slice($this->partnerOrdersSortBy($field, $orderBy, $all_partner_orders, $all_jobs)->toArray(), $offset, $limit);
    }

    public function getOrders($request)
    {
        list($field, $orderBy) = $this->getSortByFieldAdOrderFromRequest($request);
        list($offset, $limit) = calculatePagination($request);
        $filter = $request->filter;
        $partner = $request->partner->load(['partner_orders' => function ($q) use ($filter, $orderBy, $field) {
            $q->$filter()->orderBy($field, $orderBy)->with(['jobs' => function ($q) {
                $q->with('usedMaterials', 'jobServices', 'category');
            }, 'order' => function ($q) {
                $q->with(['customer.profile', 'location']);
            }]);
        }]);
        return array_slice($partner->partner_orders->each(function ($partner_order, $key) {
            $partner_order['category_name'] = $partner_order->jobs[0]->category->name;
            removeRelationsAndFields($this->getInfo($partner_order));
        })->reject(function ($item, $key) {
            return $item->order_status == 'Open';
        })->values()->all(), $offset, $limit);
    }

    public function getOrdersByClosedAt($partner, $start_time, $end_time)
    {
        return PartnerOrder::with('order.location', 'jobs.usedMaterials')
            ->where('partner_id', $partner->id)
            ->whereBetween('closed_at', [$start_time, $end_time])
            ->select('id', 'partner_id', 'order_id', 'closed_at', 'sheba_collection', 'partner_collection', 'finance_collection')
            ->get()->each(function ($partner_order) {
                $partner_order['sales'] = (double)$partner_order->calculate($price_only = true)->totalCost;
                $partner_order['week_name'] = $partner_order->closed_at->format('D');
                $partner_order['day'] = $partner_order->closed_at->toDateString();
                $partner_order['sheba_collection'] = (double)$partner_order->sheba_collection;
                $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
                $partner_order['finance_collection'] = (double)$partner_order->finance_collection;
                $partner_order['code'] = $partner_order->code();
                removeRelationsFromModel($partner_order);
            });
    }

    private function getSortByFieldAdOrderFromRequest($request)
    {
        $orderBy = 'desc';
        $field = 'created_at';
        if ($request->has('sort')) {
            $explode = explode(':', $request->get('sort'));
            $field = $explode[0];
            if (isset($explode[1]) && $explode[1] == 'asc') {
                $orderBy = 'asc';
            }
        }
        return array($field, $orderBy);
    }

    private function loadAllRelatedRelations($partner_order)
    {
        return $partner_order->load(['order.location', 'jobs' => function ($q) {
            $q->info()->orderBy('schedule_date')->with(['usedMaterials', 'resource.profile']);
        }]);
    }

    private function loadAllRelatedRelationsV2($partner_order)
    {
        return $partner_order->load(['order.location', 'jobs' => function ($q) {
            $q->info()->with(['usedMaterials', 'resource.profile', 'jobServices' => function ($q) {
                $q->with('service');
            }]);
        }]);
    }

    public function getWeeklyBreakdown($partner_orders, $start_time, $end_time)
    {
        $week = collect();
        if (count($partner_orders) > 0) {
            $partner_orders->groupBy('day')->each(function ($item, $key) use ($week) {
                $week[Carbon::parse($key)->format('D')] = $item->sum('sales');
            });
        }
        for ($date = $start_time; $date < $end_time; $date->addDay()) {
            $day = $date->format('D');
            if (!isset($week[$day])) {
                $week->put($day, 0);
            }
        }
        return $week;
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
        } elseif ($filter == 'history') {
            return constants('JOB_STATUSES');
        }
        return constants('JOB_STATUSES');
    }

    public function getInfo($partner_order)
    {
        $partner_order->calculate(true);
        $partner_order['code'] = $partner_order->code();
        $partner_order['customer_name'] = $partner_order->order->delivery_name;
        $partner_order['customer_mobile'] = $partner_order->order->delivery_mobile;
        $partner_order['address'] = $partner_order->order->delivery_address;
        $partner_order['location'] = $partner_order->order->location->name;
        $partner_order['total_price'] = (double)$partner_order->totalPrice;
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


    private function partnerOrdersSortBy($field, $orderBy, $all_partner_orders, $all_jobs)
    {
        if ($field == 'created_at') {
            $all_partner_orders = $all_partner_orders->$orderBy('created_at');
        } else {
            $all_jobs = $all_jobs->$orderBy($field);
            $final = collect();
            foreach ($all_jobs as $job) {
                $final->push($all_partner_orders->where('id', $job->partner_order_id)->first());
            }
            $all_partner_orders = $final->unique('id');
        }
        return $all_partner_orders;
    }
}