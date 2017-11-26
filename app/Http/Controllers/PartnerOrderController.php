<?php

namespace App\Http\Controllers;

use App\Repositories\PartnerRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;
use App\Http\Requests;

class PartnerOrderController extends Controller
{
    public function show($partner, Request $request)
    {
        $partner_order = $request->partner_order->load(['order', 'jobs.resource.profile']);
        $this->_getInfo($partner_order);
        removeRelationsFromModel($partner_order);
        removeSelectedFieldsFromModel($partner_order);
        return api_response($request, $partner_order, 200, ['order' => $partner_order]);
    }

    public function newOrders($partner, Request $request)
    {
        try {
            list($offset, $limit) = calculatePagination($request);
            $partner = $request->partner;
            $jobs = (new  PartnerRepository($partner))->jobs('new');
            $jobsGroupByPartnerOrder = $jobs->groupBy('partner_order_id')->sortByDesc(function ($item, $key) {
                return $key;
            });
            $final_orders = [];
            foreach ($jobsGroupByPartnerOrder as $jobs) {
                $order = array(
                    'customer_name' => $jobs[0]->partner_order->order->delivery_name,
                    'location_name' => $jobs[0]->partner_order->order->location->name,
                    'total_job' => count($jobs),
                    'created_at' => $jobs[0]->partner_order->created_at->timestamp,
                    'created_at_readable' => $jobs[0]->partner_order->created_at->diffForHumans(),
                    'code' => $jobs[0]->partner_order->code()
                );
                $jobs = $jobs->sortByDesc('created_at');
                $jobs = $jobs->each(function ($job) {
                    $job->calculate();
                    $job['total_cost'] = $job->totalCost;
                    $job['location'] = $job->partner_order->order->location->name;
                    $job['service_unit_price'] = (double)$job->service_unit_price;
                    $job['discount'] = (double)$job->discount;
                    $job['resource_picture'] = $job->resource != null ? $job->resource->profile->pro_pic : null;
                    $job['resource_mobile'] = $job->resource != null ? $job->resource->profile->mobile : null;
                    $job['rating'] = $job->review != null ? $job->review->rating : null;
                    removeRelationsFromModel($job);
                })->values()->all();
                $jobs = array_slice($jobs, $offset, $limit);
                $order['jobs'] = $jobs;
                array_push($final_orders, $order);
            }
            return api_response($request, $jobs, 200, ['orders' => $final_orders]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }

    }

    public function getOrders($partner, Request $request)
    {
        $partner = $request->partner;
        list($offset, $limit) = calculatePagination($request);
        $partner->load(['partner_orders' => function ($q) use ($offset, $limit, $request) {
            if ($request->status == 'ongoing') {
                $q->where([
                    ['cancelled_at', null],
                    ['closed_and_paid_at', null]
                ]);
            } elseif ($request->status == 'history') {
                $q->where('closed_and_paid_at', '<>', null);
            }
            $q->orderBy('id', 'desc')->skip($offset)->take($limit);
        }]);
        $partner_orders = $partner->partner_orders->load(['jobs', 'order' => function ($q) {
            $q->with(['customer.profile', 'location']);
        }]);
        $partner_orders->each(function ($partner_order, $key) {
            $this->_getInfo($partner_order);
            removeRelationsFromModel($partner_order);
            removeSelectedFieldsFromModel($partner_order);
        });
        if (count($partner_orders) > 0) {
            return api_response($request, $partner_orders, 200, ['orders' => $partner_orders]);
        } else {
            return api_response($request, null, 404);
        }
    }

    public function getOrderGraph($partner, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'month' => 'sometimes|required|integer|between:1,12',
            'year' => 'sometimes|required|integer|min:2017'
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->all()[0];
            return api_response($request, $errors, 400, ['message' => $errors]);
        }
        $partner = $request->partner;
        $month = null;
        $year = null;
        if ($request->has('month')) {
            $month = $request->month;
        }
        if ($request->has('year')) {
            $year = $request->year;
        }
        $end = Carbon::create($year, $month, null)->endOfMonth();
        $start = Carbon::create($year, $month, null)->startOfMonth();
        $breakdown = collect(array_fill(1, Carbon::create($year, $month, null)->daysInMonth, 0));
        $partner->load(['partner_orders' => function ($q) use ($start, $end) {
            $q->where([
                ['created_at', '<=', $end],
                ['created_at', '>=', $start],
                ['cancelled_at', null]
            ]);
        }]);
        $partner_orders = $partner->partner_orders;
        $day_orders = $partner_orders->groupBy('created_at.day')
            ->map(function ($item, $key) {
                return $item->count();
            })
            ->sortBy(function ($item, $key) {
                return $key;
            });
        $breakdown = $breakdown->map(function ($item, $key) use ($day_orders) {
            return $day_orders->has($key) ? $day_orders->get($key) : 0;
        });
        return api_response($request, $partner_orders, 200, ['breakdown' => $breakdown]);
    }

    private function _getInfo($partner_order)
    {
        $partner_order->calculate();
        $partner_order['code'] = $partner_order->code();
        $partner_order['customer_name'] = $partner_order->order->customer->profile->name;
        $partner_order['location'] = $partner_order->order->location->name;
        $partner_order['discount'] = (double)$partner_order->discount;
        $partner_order['sheba_collection'] = (double)$partner_order->sheba_collection;
        $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
        $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
        $partner_order['finance_collection'] = (double)$partner_order->finance_collection;
        $partner_order['discount'] = (double)$partner_order->discount;
        $partner_order['total_jobs'] = count($partner_order->jobs);
        $partner_order['order_status'] = $partner_order->status;
    }
}
