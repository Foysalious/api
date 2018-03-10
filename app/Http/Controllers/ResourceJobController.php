<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\PartnerOrder;
use App\Repositories\ResourceJobRepository;
use App\Sheba\JobTime;
use Dingo\Api\Routing\Helpers;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Validator;
use App\Http\Requests;
use DB;

class ResourceJobController extends Controller
{
    use Helpers;
    private $resourceJobRepository;

    public function __construct()
    {
        $this->resourceJobRepository = new ResourceJobRepository();
    }

    public function index($resource, Request $request)
    {
        try {
            $jobs = $this->resourceJobRepository->getJobs($request->resource);
            $jobs = $this->resourceJobRepository->rearrange($jobs);
            list($offset, $limit) = calculatePagination($request);
            $jobs = array_slice($jobs, $offset, $limit);
            if (count($jobs) != 0) {
                $jobs = $this->resourceJobRepository->addJobInformationForAPI($jobs);
                if ($request->has('group_by')) {
                    $jobs = collect($jobs)->groupBy('schedule_date');
                }
                return api_response($request, $jobs, 200, ['jobs' => $jobs]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function show($resource, $job, Request $request)
    {
        try {
            $resource = $request->resource;
            $job = $request->job;
            $job['can_process'] = false;
            $job['can_serve'] = false;
            $job['can_collect'] = false;
            $jobs = $this->api->get('v1/resources/' . $resource->id . '/jobs?remember_token=' . $resource->remember_token . '&limit=1');
            if ($jobs) {
                $job = $this->resourceJobRepository->calculateActionsForThisJob($jobs[0], $job);
            }
            return api_response($request, $job, 200, ['job' => $job]);
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function getBills($resource, $job, Request $request)
    {
        try {
            $resource = $request->resource;
            $job = $request->job->load(['partnerOrder.order', 'category', 'jobServices', 'usedMaterials' => function ($q) {
                $q->select('job_material.id', 'job_material.material_name', 'material_price', 'job_id');
            }]);
            $job->calculate(true);
            if (count($job->jobServices) == 0) {
                $services = array();
                array_push($services, array('name' => $job->category ? $job->category->name : null,
                    'price' => (double)$job->servicePrice,
                    'unit' => $job->service->unit,
                    'quantity' => $job->service_quantity));
            } else {
                $services = array();
                foreach ($job->jobServices as $jobService) {
                    array_push($services, array(
                        'name' => $jobService->job->category ? $jobService->job->category->name : null,
                        'price' => (double)$jobService->unit_price * (double)$jobService->quantity,
                        'unit' => $jobService->unit, 'quantity' => $jobService->quantity
                    ));
                }
            }
            $partnerOrder = $job->partnerOrder;
            $partnerOrder->calculate(true);
            $bill = collect();
            $bill['total'] = (double)$partnerOrder->totalPrice;
            $bill['paid'] = (double)$partnerOrder->paid;
            $bill['due'] = (double)$partnerOrder->due;
            $bill['material_price'] = (double)$job->materialCost;
            $bill['discount'] = (double)$job->discount;
            $bill['services'] = $services;
            $bill['delivered_date'] = $job->delivered_date != null ? $job->delivered_date->format('Y-m-d') : null;
            $bill['delivered_date_timestamp'] = $job->delivered_date != null ? $job->delivered_date->timestamp : null;
            $bill['closed_and_paid_at'] = $partnerOrder->closed_and_paid_at ? $partnerOrder->closed_and_paid_at->format('Y-m-d') : null;
            $bill['closed_and_paid_at_timestamp'] = $partnerOrder->closed_and_paid_at != null ? $partnerOrder->closed_and_paid_at->timestamp : null;
            $bill['status'] = $job->status;
            $bill['materials'] = $job->usedMaterials;
            $bill['isPaid'] = $job->partnerOrder->closed_at_paid ? 1 : 0;
            $bill['isDue'] = $job->partnerOrder->closed_at_paid == null ? 1 : 0;
            $bill['job_code'] = $job->fullcode();
            return api_response($request, $bill, 200, ['bill' => $bill]);
        } catch (\Exception $e) {
            dd($e);
            return api_response($request, null, 500);
        }
    }

    public function update($resource, $job, Request $request)
    {
        try {
            $job = $request->job;
            if ($request->has('status')) {
                $response = $this->resourceJobRepository->changeStatus($job->id, $request);
                if ($response) {
                    return api_response($request, $response, $response->code);
                }
                return api_response($request, null, 500);
            } elseif ($request->has('schedule_date') && $request->has('preferred_time')) {
                $job_time = new JobTime($request->schedule_date, $request->preferred_time);
                $job_time->validate();
                if ($job_time->isValid) {
                    $response = $this->resourceJobRepository->reschedule($job->id, $request);
                    if ($response) {
                        return api_response($request, $response, $response->code);
                    } else {
                        return api_response($request, null, 500);
                    }
                } else {
                    return api_response($request, null, 400, ['message' => $job_time->error_message]);
                }
            }
            return api_response($request, null, 400);
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

    public function collect($resource, $job, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric',
            ]);
            if ($validator->fails()) {
                return api_response($request, null, 500, ['message' => $validator->errors()->all()[0]]);
            }
            $partner_order = $request->job->partner_order;
            $response = $this->resourceJobRepository->collectMoney($partner_order, $request);
            if ($response) {
                return api_response($request, $response, $response->code);
            }
            return api_response($request, null, 500);
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }


    public function otherJobs($resource, $job, Request $request)
    {
        try {
            $job = $request->job;
            $job->partner_order->load(['jobs' => function ($q) {
                $q->info()->validStatus()->tillNow()->with('service');
            }]);
            $jobs = ($job->partner_order->jobs)->values()->all();
            list($offset, $limit) = calculatePagination($request);
            $jobs = array_slice($jobs, $offset, $limit);
            if (count($jobs) != 0) {
                $partner_order = $job->partner_order;
                $partner_order->order;
                $partner_order->calculate(true);
                $jobs = $this->resourceJobRepository->addJobInformationForAPI($jobs);
                return api_response($request, $jobs, 200, ['jobs' => $jobs, 'total_price' => (double)$partner_order->totalPrice, 'paid' => (double)$partner_order->paid, 'due' => (double)$partner_order->due]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Exception $e) {
            return api_response($request, null, 500);
        }
    }

}
