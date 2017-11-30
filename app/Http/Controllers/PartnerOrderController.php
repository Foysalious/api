<?php

namespace App\Http\Controllers;

use App\Repositories\PartnerRepository;
use App\Repositories\ResourceJobRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Logs\JobLogs;
use Validator;
use App\Http\Requests;

class PartnerOrderController extends Controller
{
    public function show($partner, Request $request)
    {
        try {
            $partner_order = $request->partner_order->load(['order.location', 'jobs' => function ($q) {
                $q->info()->with(['usedMaterials' => function ($q) {
                    $q->select('id', 'job_id', 'material_name', 'material_price');
                }, 'resource.profile']);
            }]);
            $this->_getInfo($partner_order);
            $jobs = $partner_order->jobs;
            $jobs = $jobs->each(function ($job) use ($partner_order) {
                $job['partner_order'] = $partner_order;
                $this->_getJobInfo($job);
                removeSelectedFieldsFromModel($job);
                removeRelationsFromModel($job);
                array_forget($job, 'partner_order');
            });
            removeRelationsFromModel($partner_order);
            removeSelectedFieldsFromModel($partner_order);
            $partner_order['jobs'] = $jobs->sortBy('schedule_date');
            return api_response($request, $partner_order, 200, ['order' => $partner_order]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
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
            list($offset, $limit) = calculatePagination($request);
            $partner = $request->partner;
            $partnerRepo = new PartnerRepository($partner);
            $statuses = $partnerRepo->resolveStatus('new');
            $jobs = $partnerRepo->jobs($statuses);
            $jobsGroupByPartnerOrder = $jobs->groupBy('partner_order_id');
            $final_orders = collect();
            $all_jobs = collect();
            foreach ($jobsGroupByPartnerOrder as $jobs) {
                $order = collect();
                $order->put('customer_name', $jobs[0]->partner_order->order->delivery_name);
                $order->put('location_name', $jobs[0]->partner_order->order->location->name);
                $order->put('total_job', count($jobs));
                $order->put('created_at', $jobs[0]->partner_order->created_at->timestamp);
                $order->put('created_at_readable', $jobs[0]->partner_order->created_at->diffForHumans());
                $order->put('code', $jobs[0]->partner_order->code());
                $order->put('id', $jobs[0]->partner_order->id);
                $order->put('jobs', $jobs->each(function ($job) use ($order, $all_jobs) {
                    $this->_getJobInfo($job);
                    removeSelectedFieldsFromModel($job);
                    removeRelationsFromModel($job);
                }));
                foreach ($order->get('jobs') as $job) {
                    $all_jobs->push($job);
                }
                $final_orders->push($order);
            }
            if (count($final_orders) > 0) {
                if ($field == 'created_at') {
                    $final_orders = $final_orders->$sort('created_at')->toArray();
                } else {
                    $sorted_jobs = $all_jobs->$sort($field);
                    $final = collect();
                    foreach ($sorted_jobs as $job) {
                        $final->push($final_orders->where('id', $job->partner_order_id)->first());
                    }
                    $final_orders = $final->unique('id')->toArray();
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
            $validator = Validator::make($request->all(), [
                'sort' => 'sometimes|required|string|in:created_at,created_at:asc,created_at:desc',
                'status' => 'required|in:ongoing,history'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors()->all()[0];
                return api_response($request, $errors, 400, ['message' => $errors]);
            }
            $sort = 'desc';
            $field = 'created_at';
            if ($request->has('sort')) {
                $explode = explode(':', $request->get('sort'));
                if (isset($explode[1])) {
                    $sort = $explode[1];
                }
            }
            $partner = $request->partner;
            list($offset, $limit) = calculatePagination($request);
            $partner->load(['partner_orders' => function ($q) use ($request, $sort, $field) {
                if ($request->status == 'ongoing') {
                    $q->where([
                        ['cancelled_at', null],
                        ['closed_and_paid_at', null]
                    ]);
                } elseif ($request->status == 'history') {
                    $q->where('closed_and_paid_at', '<>', null);
                }
                $q->orderBy($field, $sort)->with(['jobs.usedMaterials', 'order' => function ($q) {
                    $q->with(['customer.profile', 'location']);
                }]);
            }]);
            $partner_orders = $partner->partner_orders->each(function ($partner_order, $key) {
                $this->_getInfo($partner_order);
                removeRelationsFromModel($partner_order);
                removeSelectedFieldsFromModel($partner_order);
            });
            $partner_orders = array_slice($partner_orders->reject(function ($item, $key) {
                return $item->order_status == 'Open';
            })->toArray(), $offset, $limit);
            if (count($partner_orders) > 0) {
                return api_response($request, $partner_orders, 200, ['orders' => $partner_orders]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getBills($partner, Request $request)
    {
        try {
            $partner_order = $request->partner_order->load(['order', 'jobs' => function ($q) {
                $q->info()->with(['service', 'usedMaterials' => function ($q) {
                    $q->select('id', 'job_id', 'material_name', 'material_price');
                }]);
            }]);
            $jobs = (new ResourceJobRepository())->addJobInformationForAPI($partner_order->jobs->each(function ($item) use ($partner_order) {
                $item['partner_order'] = $partner_order;
            }));
            $partner_order->calculate();
            $partner_order['paid_amount'] = (double)$partner_order->paid;
            $partner_order['due_amount'] = (double)$partner_order->due;
            $partner_order['total'] = (double)$partner_order->totalPrice;
            $partner_order['is_paid'] = ((double)$partner_order->due == 0) ? true : false;
            $partner_order['is_due'] = ((double)$partner_order->due > 0) ? true : false;
            $partner_order['is_closed'] = ($partner_order->closed_at != null) ? true : false;
            $partner_order['order_status'] = $partner_order->status;
            if ($partner_order['is_closed']) {
                $partner_order['overdue'] = $partner_order->closed_at->diffInDays(Carbon::now());
            } else {
                $partner_order['overdue'] = null;
            }
            removeRelationsFromModel($partner_order);
            removeSelectedFieldsFromModel($partner_order);
            $partner_order['jobs'] = $jobs->each(function ($item) {
                removeRelationsFromModel($item);
            });
            return api_response($request, $partner_order, 200, ['order' => $partner_order]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function getLogs($partner, Request $request)
    {
        try {
            if ($request->has('filter')) {
                $filter = $request->filter;
                $logs = $request->partner_order->$filter->where('transaction_type', 'Debit');
                if (count($logs) == 0) {
                    return api_response($request, $logs, 404);
                }
                $logs->each(function ($item, $key) {
                    $item['amount'] = (double)$item->amount;
                    $item['collected_by'] = trim(explode('-', $item->created_by_name)[1]);
                    removeSelectedFieldsFromModel($item);
                });
                return api_response($request, $logs, 200, ['logs' => $logs]);
            }
            $jobs = $request->partner_order->jobs;
            $all_logs = collect();
            foreach ($jobs as $job) {
                $all_logs->push((new JobLogs($job))->all());
            }
            dd($all_logs);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private
    function _getInfo($partner_order)
    {
        $partner_order->calculate();
        $partner_order['due_amount'] = (double)$partner_order->due;
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
    }

    private
    function _getJobInfo($job)
    {
        $job->calculate();
        $job['total_cost'] = $job->totalCost;
        $job['location'] = $job->partner_order->order->location->name;
        $job['service_unit_price'] = (double)$job->service_unit_price;
        $job['discount'] = (double)$job->discount;
        $job['resource_picture'] = $job->resource != null ? $job->resource->profile->pro_pic : null;
        $job['resource_name'] = $job->resource != null ? $job->resource->profile->name : null;
        $job['resource_mobile'] = $job->resource != null ? $job->resource->profile->mobile : null;
        $job['materials'] = $job->usedMaterials;
        $job['total_materials'] = count($job->usedMaterials);
    }
}
