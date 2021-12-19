<?php namespace App\Http\Controllers;

use App\Repositories\ResourceJobRepository;
use Sheba\Jobs\JobTime;
use Carbon\Carbon;
use DB;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Jobs\PreferredTime;
use Validator;

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
            if (count($jobs) != 0) {
                $jobs = $this->resourceJobRepository->addJobInformationForAPI($jobs);
                $group_by_jobs = collect($jobs)->groupBy('schedule_date')->sortBy(function ($item, $key) {
                    return $key;
                });
                $final = collect();
                foreach ($group_by_jobs as $key => $jobs) {
                    $jobs = $jobs->sortBy('schedule_timestamp');
                    foreach ($jobs as $job) {
                        $final->push($job);
                    }
                }
                $other_jobs = $final->reject(function ($job) {
                    return $job->status == 'Served';
                });
                $jobs = $final->where('status', 'Served')->merge($other_jobs);
                if ($request->filled('group_by')) {
                    $jobs = collect($jobs)->groupBy('schedule_date');
                } elseif ($request->filled('sort_by')) {
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
                $jobs = $jobs->splice($offset, $limit);
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
            $job['collect_money'] = 0;
            $jobs = $this->api->get('v1/resources/' . $resource->id . '/jobs?remember_token=' . $resource->remember_token . '&limit=1');
            if ($jobs) {
                $first_job_from_list = $jobs[0];
                if ($job->id == $first_job_from_list->id) {
                    $job->partner_order->calculate(true);
                    $partner_order = $job->partner_order;
                    $job = $this->resourceJobRepository->calculateActionsForThisJob($job);
                    if ($partner_order->closed_and_paid_at == null) {
                        $job['collect_money'] = $partner_order->due;
                    }
                }
            }
            removeRelationsAndFields($job);
            $job = array(
                'id' => $job->id,
                'status' => $job->status,
                'resource_id' => $job->resource_id,
                'partner_order_id' => $job->partner_order_id,
                'can_process' => $job->can_process,
                'can_serve' => $job->can_serve,
                'can_collect' => $job->can_collect,
                'collect_money' => (double)$job->collect_money,
            );
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
                        'unit' => $jobService->service->unit, 'quantity' => $jobService->quantity
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
            if ($request->filled('status')) {
                $request->merge(['partner' => $job->partnerOrder->partner]);
                $response = $this->resourceJobRepository->changeStatus($job->id, $request);
                if ($response) {
                    return api_response($request, $response, $response->code);
                }
                return api_response($request, null, 500);
            } elseif ($request->filled('schedule_date') && $request->filled('preferred_time')) {
                $job_time = new JobTime($request->schedule_date, $request->preferred_time);
                $job_time->validate();
                if ($job_time->isValid) {
                    $preferred_time = new PreferredTime($request->preferred_time);
                    if (!scheduler($job->resource)->isAvailableForCategory($request->schedule_date, $preferred_time->getStartString(), $job->category, $job)) {
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
            if ($response) return api_response($request, $response, $response->code, ['message' => $response->msg]);
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
