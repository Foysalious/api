<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\PartnerOrder;
use App\Models\Resource;
use App\Repositories\ResourceJobRepository;
use App\Sheba\JobTime;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
                } elseif ($request->has('sort_by')) {
                    $jobs = collect($jobs);
                    $schedule_due_jobs = $jobs->filter(function ($job) {
                        return (Carbon::parse($job->schedule_date) < Carbon::today() && $job->status == 'Schedule Due');
                    })->values()->all();
                    $todays_jobs = $jobs->filter(function ($job) {
                        return (Carbon::parse($job->schedule_date))->format('Y-m-d') == (Carbon::today())->format('Y-m-d') && $job->status != 'Served';
                    })->values()->all();
                    $payment_due_jobs = $jobs->filter(function ($job) {
                        return $job->isDue == 1 && $job->delivered_date != null;
                    })->values()->all();
                    return api_response($request, $jobs, 200, ['schedule_due' => $schedule_due_jobs, 'today' => $todays_jobs, 'payment_due' => $payment_due_jobs]);
                }
                return api_response($request, $jobs, 200, ['jobs' => $jobs]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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

            $job['pick_up_address'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->pick_up_address : null;
            $job['destination_address'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->destination_address : null;
            $job['drop_off_date'] = $job->carRentalJobDetail ? Carbon::parse($job->carRentalJobDetail->drop_off_date)->format('jS F, Y') : null;
            $job['drop_off_time'] = $job->carRentalJobDetail ? Carbon::parse($job->carRentalJobDetail->drop_off_time)->format('g:i A') : null;
            $job['estimated_distance'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->estimated_distance : null;
            $job['estimated_time'] = $job->carRentalJobDetail ? $job->carRentalJobDetail->estimated_time : null;
            array_forget($job, 'carRentalJobDetail');

            return api_response($request, $job, 200, ['job' => $job]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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
            $bill['total_material_price'] = (double)$job->materialPrice;
            $bill['total_service_price'] = (double)$job->servicePrice;
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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
                    if (!scheduler($job->resource)->isAvailableForCategory($request->schedule_date, explode('-', $request->preferred_time)[0], $job->category)) {
                        return api_response($request, null, 403, ['message' => 'Resource is not available at this time. Please select different date time or change the resource']);
                    }
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function collect($resource, $job, Request $request)
    {
        try {
            $this->validate($request, ['amount' => 'required|numeric']);
            $partner_order = $request->job->partner_order;
            $response = $this->resourceJobRepository->collectMoney($partner_order, $request);
            if ($response) return api_response($request, $response, $response->code);
            return api_response($request, null, 500);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}
