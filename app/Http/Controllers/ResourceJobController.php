<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class ResourceJobController extends Controller
{
    public function index($resource, Request $request)
    {
        $resource = $request->resource;
        $resource->load(['jobs' => function ($q) {
            $q->select('id', 'resource_id', 'schedule_date', 'service_name', 'status', 'partner_order_id')->orderBy('schedule_date')->whereIn('status', ['Accepted', 'Schedule Due'])->with(['partner_order' => function ($q) {
                $q->with('order.customer.profile');
            }]);
        }]);
        $jobs = $resource->jobs;
        if (count($jobs) != 0) {
            foreach ($jobs as $job) {
                $job['customer_name'] = $job->partner_order->order->customer->profile->name;
                $job['address'] = $job->partner_order->order->delivery_address;
                $job['code'] = $job->code();
                array_forget($job, 'partner_order');
                array_forget($job, 'partner_order_id');
                array_forget($job, 'resource_id');
            }
            list($offset, $limit) = calculatePagination($request);
            $jobs = $jobs->splice($offset, $limit)->all();
            return api_response($request, $jobs, 200, ['jobs' => $jobs]);
        } else {
            return api_response($request, null, 404);
        }
    }
}
