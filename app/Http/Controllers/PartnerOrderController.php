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
        $partner_order = $request->partner_order->load(['order', 'jobs' => function ($q) {
            $q->info()->with(['usedMaterials' => function ($q) {
                $q->select('id', 'job_id', 'material_name', 'material_price');
            }, 'resource.profile']);
        }]);
        $this->_getInfo($partner_order);
        $jobs = $partner_order->jobs->each(function ($job) {
            $this->_getJobInfo($job);
        });
        $partner_order['jobs'] = $jobs;
        return api_response($request, $partner_order, 200, ['order' => $partner_order]);
    }

    public function newOrders($partner, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'sort' => 'sometimes|required|string|in:created_at,created_at:asc,created_at:desc,jobs.schedule_date,jobs.schedule_date:asc,jobs.schedule_date:desc'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            list($offset, $limit) = calculatePagination($request);
            $partner = $request->partner;
            $partnerRepo = new PartnerRepository($partner);
            $statuses = $partnerRepo->resolveStatus('new');
            $jobs = $partnerRepo->jobs($statuses);
            $sort = 'sortByDesc';
            $field = 'created_at';
            if ($request->has('sort')) {
                $explode = explode(':', $request->get('sort'));
                $column = explode('.', $explode[0]);
                if (isset($column[1])) {
                    $field = $column[1];
                }
                if (isset($explode[1]) && $explode[1] == 'asc') {
                    $sort = 'sortBy';
                }
            }
            $jobsGroupByPartnerOrder = $jobs->groupBy('partner_order_id');
            $final_orders = collect();
            foreach ($jobsGroupByPartnerOrder as $jobs) {
                $order = collect();
                $order->put('customer_name', $jobs[0]->partner_order->order->delivery_name);
                $order->put('location_name', $jobs[0]->partner_order->order->location->name);
                $order->put('total_job', count($jobs));
                $order->put('created_at', $jobs[0]->partner_order->created_at->timestamp);
                $order->put('created_at_readable', $jobs[0]->partner_order->created_at->diffForHumans());
                $order->put('code', $jobs[0]->partner_order->code());
                $order->put('jobs', $jobs->each(function ($job) use ($order) {
                    $this->_getJobInfo($job);
                }));
                $final_orders->push($order);
            }
            if (count($final_orders) > 0) {
                if ($field == 'created_by') {
                    $final_orders = $final_orders->$sort('created_by')->toArray();
                } else {
                    $final_orders = $final_orders->$sort(function ($item, $key) use ($sort, $field) {
                        return min($item->get('jobs')->pluck($field)->toArray());
                    })->toArray();
                }
                $final_orders = array_slice($final_orders, $offset, $limit);
                return api_response($request, $final_orders, 200, ['orders' => $final_orders]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }

    }

    public function getOrders($partner, Request $request)
    {
        try {
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
            });
            if (count($partner_orders) > 0) {
                return api_response($request, $partner_orders, 200, ['orders' => $partner_orders]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }


    private function _getInfo($partner_order)
    {
        $partner_order->calculate();
        $partner_order['code'] = $partner_order->code();
        $partner_order['customer_name'] = $partner_order->order->delivery_name;
        $partner_order['customer_mobile'] = $partner_order->order->delivery_mobile;
        $partner_order['address'] = $partner_order->order->delivery_address;
        $partner_order['location'] = $partner_order->order->location->name;
        $partner_order['discount'] = (double)$partner_order->discount;
        $partner_order['sheba_collection'] = (double)$partner_order->sheba_collection;
        $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
        $partner_order['partner_collection'] = (double)$partner_order->partner_collection;
        $partner_order['finance_collection'] = (double)$partner_order->finance_collection;
        $partner_order['discount'] = (double)$partner_order->discount;
        $partner_order['total_jobs'] = count($partner_order->jobs);
        $partner_order['order_status'] = $partner_order->status;
        removeRelationsFromModel($partner_order);
        removeSelectedFieldsFromModel($partner_order);
    }

    private function _getJobInfo($job)
    {
        $job->calculate();
        $job['total_cost'] = $job->totalCost;
        $job['location'] = $job->partner_order->order->location->name;
        $job['service_unit_price'] = (double)$job->service_unit_price;
        $job['discount'] = (double)$job->discount;
        $job['resource_picture'] = $job->resource != null ? $job->resource->profile->pro_pic : null;
        $job['resource_mobile'] = $job->resource != null ? $job->resource->profile->mobile : null;
        $job['materials'] = $job->usedMaterials;
        removeRelationsFromModel($job);
    }
}
