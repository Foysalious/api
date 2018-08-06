<?php

namespace App\Repositories;

use App\Models\PartnerOrder;
use App\Sheba\JobTime;
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
            $job->jobServices->each(function ($job_service) use (&$services, $job) {
                $info = $this->partnerJobRepository->getJobServiceInfo($job_service);
                $info['name'] = $job_service->formatServiceName($job);
                $info['unit'] = $job_service->service->unit;
                $info['discount'] = (double)$job_service->discount;
                $info['sheba_contribution'] = (double)$job_service->sheba_contribution;
                $info['partner_contribution'] = (double)$job_service->partner_contribution;
                $info['sheba_contribution_amount'] = round(($info['discount'] * $info['sheba_contribution']) / 100, 2);
                $info['partner_contribution_amount'] = round(($info['discount'] * $info['partner_contribution']) / 100, 2);
                array_push($services, $info);
            });

            $job['category_name'] = $job->category ? $job->category->name : null;
            removeRelationsAndFields($job);
            $job['services'] = $services;

            $job['pick_up_address'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->pick_up_address : null;
            $job['destination_address'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->destination_address : null;
            $job['drop_off_date'] = $job->carRentalJobDetail ? Carbon::parse($job->carRentalJobDetail->drop_off_date)->format('jS F, Y') : null;
            $job['drop_off_time'] = $job->carRentalJobDetail ? Carbon::parse($job->carRentalJobDetail->drop_off_time)->format('g:i A') : null;
            $job['estimated_distance'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->estimated_distance : null;
            $job['estimated_time'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->estimated_time : null;

            array_forget($job, ['partner_order', 'carRentalJobDetail']);
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
            $job = $jobs[0];
            $services = collect();
            if (count($job->jobServices) == 0) {
                $variables = json_decode($job->service_variables);
                $services->push(array('name' => $job->service_name, 'variables' => $variables, 'quantity' => (double)$job->quantity));
            } else {
                foreach ($job->jobServices as $jobService) {
                    $variables = json_decode($jobService->variables);
                    $services->push(array('name' => $jobService->service->name, 'variables' => $variables, 'quantity' => (double)$jobService->quantity));
                }
            }
            $order = collect([
                'customer_name' => $jobs[0]->partner_order->order->delivery_name,
                'address' => $jobs[0]->partner_order->order->delivery_address,
                'location_name' => $jobs[0]->partner_order->order->location->name,
                'created_at' => $jobs[0]->partner_order->created_at->timestamp,
                'created_at_readable' => $jobs[0]->partner_order->created_at->diffForHumans(),
                'code' => $jobs[0]->partner_order->code(),
                'id' => $jobs[0]->partner_order->id,
                'total_price' => (double)$jobs[0]->partner_order->totalPrice,
                'discount' => (double)$jobs[0]->partner_order->totalDiscount,
                'category_name' => $jobs[0]->category ? $jobs[0]->category->name : null,
                'job_id' => $jobs[0]->id,
                'schedule_date' => $jobs[0]->schedule_date,
                'preferred_time' => $jobs[0]->readable_preferred_time,
                'services' => $services
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
                $q->with('usedMaterials', 'jobServices', 'category', 'resource.profile', 'review');
            }, 'order' => function ($q) {
                $q->with(['customer.profile', 'location']);
            }]);
        }]);
        return array_slice($partner->partner_orders->each(function ($partner_order, $key) {
            $partner_order['version'] = $partner_order->is_v2 ? 'v2' : 'v1';
            $partner_order['category_name'] = $partner_order->jobs[0]->category ? $partner_order->jobs[0]->category->name : null;
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
            $q->info()->orderBy('schedule_date')->with(['usedMaterials', 'resource.profile', 'review' => function ($q) {
                $q->with('customer.profile');
            }]);
        }]);
    }

    private function loadAllRelatedRelationsV2($partner_order)
    {
        return $partner_order->load(['order.location', 'jobs' => function ($q) {
            $q->info()->with(['usedMaterials', 'carRentalJobDetail', 'resource.profile', 'jobServices' => function ($q) {
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

    public function getStatusFromRequest($request)
    {
        if ($request->has('status')) {
            return explode(',', $request->status);
        } elseif ($request->has('filter')) {
            return $this->resolveStatus($request->filter);
        } else {
            return array(constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Pending'], constants('JOB_STATUSES')['Not_Responded'],
                constants('JOB_STATUSES')['Declined'], constants('JOB_STATUSES')['Cancelled'],
                constants('JOB_STATUSES')['Schedule_Due'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Serve_Due'], constants('JOB_STATUSES')['Served']);
        }
    }

    private function resolveStatus($filter)
    {
        if ($filter == 'ongoing') {
            return array(constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Schedule_Due'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Serve_Due'], constants('JOB_STATUSES')['Served']);
        } elseif ($filter == 'history') {
            return constants('JOB_STATUSES');
        }
    }

    public function getInfo($partner_order)
    {
        if ($partner_order->jobs->count() > 1) {
            $job = $partner_order->jobs->whereIn('status', array(constants('JOB_STATUSES')['Accepted'], constants('JOB_STATUSES')['Pending'], constants('JOB_STATUSES')['Not_Responded'],
                constants('JOB_STATUSES')['Schedule_Due'], constants('JOB_STATUSES')['Process'], constants('JOB_STATUSES')['Serve_Due'], constants('JOB_STATUSES')['Served']))->first();
        } else {
            $job = $partner_order->jobs->first();
        }
        $partner_order->calculate(true);
        $partner_order['code'] = $partner_order->code();
        $partner_order['customer_name'] = $partner_order->order->delivery_name;
        $partner_order['customer_mobile'] = $partner_order->order->delivery_mobile;
        $partner_order['resource_picture'] = $job->resource ? $job->resource->profile->pro_pic : null;
        $partner_order['resource_mobile'] = $job->resource ? $job->resource->profile->mobile : null;
        $partner_order['category_app_banner'] = $job->category ? $job->category->app_banner : null;
        $partner_order['category_banner'] = $job->category ? $job->category->banner : null;
        $partner_order['rating'] = $job->review ? (double)$job->review->rating : null;
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
        $partner_order['order_status'] = $job->status;
        $partner_order['isRentCar'] = $job->isRentCar();
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