<?php

namespace App\Http\Controllers;

use App\Repositories\ResourceJobRepository;
use Illuminate\Http\Request;

use App\Http\Requests;

class ResourceJobController extends Controller
{
    private $resourceJobRepository;

    public function __construct()
    {
        $this->resourceJobRepository = new ResourceJobRepository();
    }

    public function index($resource, Request $request)
    {
        $resource = $request->resource->load(['jobs' => function ($q) {
            $q->select('id', 'resource_id', 'schedule_date', 'preferred_time', 'service_name', 'status', 'partner_order_id')->where('schedule_date', '<=', date('Y-m-d'))->whereIn('status', ['Accepted', 'Process', 'Schedule Due'])->with(['partner_order' => function ($q) {
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
            $jobs = $this->resourceJobRepository->rearrange($jobs);
            list($offset, $limit) = calculatePagination($request);
            $jobs = array_slice($jobs, $offset, $limit);
            return api_response($request, $jobs, 200, ['jobs' => $jobs]);
        } else {
            return api_response($request, null, 404);
        }
    }
}
