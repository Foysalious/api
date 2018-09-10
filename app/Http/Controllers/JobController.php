<?php

namespace App\Http\Controllers;

use App\Models\CustomerFavorite;
use App\Models\Job;
use App\Repositories\JobCancelLogRepository;
use App\Sheba\Checkout\OnlinePayment;
use App\Sheba\JobStatus;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Sheba\Logs\Customer\JobLogs;
use Sheba\OnlinePayment\Bkash;
use Sheba\OnlinePayment\Payment;
use Sheba\PayCharge\Adapters\OrderAdapter;
use Sheba\PayCharge\PayCharge;

class JobController extends Controller
{
    private $job_statuses_show;
    private $job_statuses;

    public function __construct()
    {
        $this->job_statuses_show = config('constants.JOB_STATUSES_SHOW');
        $this->job_statuses = config('constants.JOB_STATUSES');
    }

    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'filter' => 'sometimes|string|in:ongoing,history'
            ]);
            $filter = $request->has('filter') ? $request->filter : null;
            $customer = $request->customer->load(['orders' => function ($q) use ($filter) {
                $q->with(['partnerOrders' => function ($q) use ($filter) {
                    if ($filter) {
                        $q->$filter();
                    }
                    $q->with(['partner', 'jobs' => function ($q) {
                        $q->with(['resource.profile', 'category', 'review']);
                    }]);
                }]);
            }]);
            $all_jobs = $this->getJobOfOrders($customer->orders->filter(function ($order) {
                return $order->partnerOrders->count() > 0;
            }))->sortByDesc('created_at');
            return count($all_jobs) > 0 ? api_response($request, $all_jobs, 200, ['orders' => $all_jobs->values()->all()]) : api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function show($customer, $job, Request $request)
    {
        try {
            $customer = $request->customer;
            $job = $request->job->load(['resource.profile', 'carRentalJobDetail', 'category', 'review', 'jobServices', 'complains' => function ($q) use ($customer) {
                $q->select('id', 'job_id', 'status', 'complain', 'complain_preset_id')
                    ->whereHas('accessor', function ($query) use ($customer) {
                        $query->where('accessors.model_name', get_class($customer));
                    });
            }]);

            $job->partnerOrder->calculate(true);
            $job_collection = collect();
            $job_collection->put('id', $job->id);
            $job_collection->put('resource_name', $job->resource ? $job->resource->profile->name : null);
            $job_collection->put('resource_picture', $job->resource ? $job->resource->profile->pro_pic : null);
            $job_collection->put('resource_mobile', $job->resource ? $job->resource->profile->mobile : null);
            $job_collection->put('delivery_address', $job->partnerOrder->order->delivery_address);
            $job_collection->put('delivery_name', $job->partnerOrder->order->delivery_name);
            $job_collection->put('delivery_mobile', $job->partnerOrder->order->delivery_mobile);
            $job_collection->put('additional_information', $job->job_additional_info);
            $job_collection->put('schedule_date', $job->schedule_date);
            $job_collection->put('schedule_date_readable', (Carbon::parse($job->schedule_date))->format('jS F, Y'));
            $job_collection->put('complains', $this->formatComplains($job->complains));
            $job_collection->put('preferred_time', $job->readable_preferred_time);
            $job_collection->put('category_name', $job->category ? $job->category->name : null);
            $job_collection->put('partner_name', $job->partnerOrder->partner->name);
            $job_collection->put('status', $job->status);
            $job_collection->put('rating', $job->review ? $job->review->rating : null);
            $job_collection->put('review', $job->review ? $job->review->calculated_review : null);
            $job_collection->put('price', (double)$job->partnerOrder->totalPrice);
            $job_collection->put('isDue', (double)$job->partnerOrder->due > 0 ? 1 : 0);
            $job_collection->put('isRentCar', $job->isRentCar());
            $job_collection->put('order_code', $job->partnerOrder->order->code());
            $job_collection->put('pick_up_address', $job->carRentalJobDetail ? $job->carRentalJobDetail->pick_up_address : null);
            $job_collection->put('destination_address', $job->carRentalJobDetail ? $job->carRentalJobDetail->destination_address : null);
            $job_collection->put('drop_off_date', $job->carRentalJobDetail ? (Carbon::parse($job->carRentalJobDetail->drop_off_date)->format('jS F, Y')) : null);
            $job_collection->put('drop_off_time', $job->carRentalJobDetail ? (Carbon::parse($job->carRentalJobDetail->drop_off_time)->format('g:i A')) : null);
            $job_collection->put('estimated_distance', $job->carRentalJobDetail ? $job->carRentalJobDetail->estimated_distance : null);
            $job_collection->put('estimated_time', $job->carRentalJobDetail ? $job->carRentalJobDetail->estimated_time : null);

            if (count($job->jobServices) == 0) {
                $services = collect();
                $variables = json_decode($job->service_variables);
                $services->push(array('name' => $job->service_name, 'variables' => $variables, 'quantity' => $job->service_quantity, 'unit' => $job->service->unit));
            } else {
                $services = collect();
                foreach ($job->jobServices as $jobService) {
                    $variables = json_decode($jobService->variables);
                    $services->push(array('name' => $jobService->formatServiceName($job), 'variables' => $variables, 'unit' => $jobService->service->unit, 'quantity' => $jobService->quantity));
                }
            }
            $job_collection->put('services', $services);
            return api_response($request, $job_collection, 200, ['job' => $job_collection]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function formatComplains($complains)
    {
        foreach ($complains as &$complain) {
            $complain['code'] = $complain->code();
        }
        return $complains;
    }

    public function getBills($customer, $job, Request $request)
    {
        try {
            $job = $request->job->load(['partnerOrder.order', 'category', 'service', 'jobServices' => function ($q) {
                $q->with('service');
            }]);
            $job->calculate(true);
            if (count($job->jobServices) == 0) {
                $services = array();
                array_push($services, array(
                    'name' => $job->service != null ? $job->service->name : null,
                    'price' => (double)$job->servicePrice,
                    'min_price' => 0, 'is_min_price_applied' => 0
                ));
            } else {
                $services = array();
                foreach ($job->jobServices as $jobService) {
                    $total = (double)$jobService->unit_price * (double)$jobService->quantity;
                    $min_price = (double)$jobService->min_price;
                    array_push($services, array(
                        'name' => $jobService->service != null ? $jobService->service->name : null,
                        'price' => $total,
                        'min_price' => $min_price,
                        'is_min_price_applied' => $min_price > $total ? 1 : 0
                    ));
                }
            }
            $partnerOrder = $job->partnerOrder;
            $partnerOrder->calculate(true);
            $bill = collect();
            $bill['total'] = (double)$partnerOrder->totalPrice;
            $bill['paid'] = (double)$partnerOrder->paid;
            $bill['due'] = (double)$partnerOrder->due;
            $bill['material_price'] = (double)$job->materialPrice;
            $bill['discount'] = (double)$job->discount;
            $bill['services'] = $services;
            $bill['delivered_date'] = $job->delivered_date != null ? $job->delivered_date->format('Y-m-d') : null;
            $bill['delivered_date_timestamp'] = $job->delivered_date != null ? $job->delivered_date->timestamp : null;
            $bill['closed_and_paid_at'] = $partnerOrder->closed_and_paid_at ? $partnerOrder->closed_and_paid_at->format('Y-m-d') : null;
            $bill['closed_and_paid_at_timestamp'] = $partnerOrder->closed_and_paid_at != null ? $partnerOrder->closed_and_paid_at->timestamp : null;
            $bill['status'] = $job->status;
            $bill['invoice'] = $job->partnerOrder->invoice;
            $bill['version'] = $job->partnerOrder->getVersion();
            return api_response($request, $bill, 200, ['bill' => $bill]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getLogs($customer, $job, Request $request)
    {
        try {
            $all_logs = collect();
            $this->formatLogs((new JobLogs($request->job))->all(), $all_logs);
            $dates = $all_logs->sortByDesc(function ($item, $key) {
                return $item->get('timestamp');
            });
            return count($dates) > 0 ? api_response($request, $dates, 200, ['logs' => $dates->values()->all()]) : api_response($request, null, 404);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function formatLogs($job_logs, $all_logs)
    {
        foreach ($job_logs as $key => $job_log) {
            foreach ($job_log as $log) {
                $collect = collect($log);
                $collect->put('created_at', $log->created_at->toDateString());
                $collect->put('timestamp', $log->created_at->timestamp);
                $collect->put('type', $key);
                $collect->put('color_code', '#02adfc');
                $all_logs->push($collect);
            }
        }
    }

    private function getJobOfOrders($orders)
    {
        $all_jobs = collect();
        foreach ($orders as $order) {
            foreach ($order->partnerOrders as $partnerOrder) {
                $partnerOrder->calculateStatus();
                foreach ($partnerOrder->jobs as $job) {
                    $category = $job->category == null ? $job->service->category : $job->category;
                    $all_jobs->push(collect(array(
                        'job_id' => $job->id,
                        'category_name' => $category->name,
                        'category_thumb' => $category->thumb,
                        'schedule_date' => $job->schedule_date ? $job->schedule_date : null,
                        'preferred_time' => $job->preferred_time ? humanReadableShebaTime($job->preferred_time) : null,
                        'status' => $job->status,
                        'status_color' => constants('JOB_STATUSES_COLOR')[$job->status]['customer'],
                        'partner_name' => $partnerOrder->partner->name,
                        'rating' => $job->review != null ? $job->review->rating : null,
                        'order_code' => $order->code(),
                        'created_at' => $job->created_at->format('Y-m-d'),
                        'created_at_timestamp' => $job->created_at->timestamp
                    )));
                }
            }
        }
        return $all_jobs;
    }

    public function getInfo($customer, $job, Request $request)
    {
        $job = Job::find($job);
        if ($job != null) {
            if ($job->partner_order->order->customer_id == $customer) {
                $job = Job::with(['partner_order' => function ($query) {
                    $query->select('id', 'partner_id', 'order_id')->with(['partner' => function ($query) {
                        $query->select('id', 'name');
                    }])->with(['order' => function ($query) {
                        $query->select('id');
                    }]);
                }])->with(['resource' => function ($q) {
                    $q->select('id', 'profile_id')->with(['profile' => function ($q) {
                        $q->select('id', 'name', 'mobile', 'pro_pic');
                    }]);
                }])->with(['usedMaterials' => function ($query) {
                    $query->select('id', 'job_id', 'material_name', 'material_price');
                }])->with(['service' => function ($query) {
                    $query->select('id', 'name', 'unit');
                }])->with(['review' => function ($query) {
                    $query->select('job_id', 'review_title', 'review', 'rating');
                }])->where('id', $job->id)
                    ->select('id', 'service_id', 'resource_id', DB::raw('DATE_FORMAT(schedule_date, "%M %d, %Y") as schedule_date'),
                        DB::raw('DATE_FORMAT(delivered_date, "%M %d, %Y at %h:%i %p") as delivered_date'), 'created_at', 'preferred_time',
                        'service_name', 'service_quantity', 'service_variable_type', 'service_variables', 'job_additional_info', 'service_option', 'discount',
                        'status', 'service_unit_price', 'partner_order_id')
                    ->first();
                array_add($job, 'status_show', $this->job_statuses_show[array_search($job->status, $this->job_statuses)]);

                $job_model = Job::find($job->id);
                $job_model->calculate();
                array_add($job, 'material_price', $job_model->materialPrice);
                array_add($job, 'total_cost', $job_model->grossPrice);
                array_add($job, 'job_code', $job_model->fullCode());
                array_add($job, 'time', $job->created_at->format('jS M, Y'));
                array_forget($job, 'created_at');
                array_add($job, 'service_price', $job_model->servicePrice);
                if ($job->resource != null) {
                    $profile = $job->resource->profile;
                    array_forget($job, 'resource');
                    $job['resource'] = $profile;
                } else {
                    $job['resource'] = null;
                }

                return response()->json(['job' => $job, 'msg' => 'successful', 'code' => 200]);
            } else {
                return response()->json(['msg' => 'unauthorized', 'code' => 409]);
            }
        } else {
            return api_response($request, null, 404);
        }
    }

    public function getPreferredTimes()
    {
        return response()->json(['times' => config('constants.JOB_PREFERRED_TIMES'), 'valid_times' => $this->getSelectableTimes(), 'code' => 200]);
    }

    private function getSelectableTimes()
    {
        $today_slots = [];
        foreach (constants('JOB_PREFERRED_TIMES') as $time) {
            if ($time == "Anytime" || Carbon::now()->lte(Carbon::createFromTimestamp(strtotime(explode(' - ', $time)[1])))) {
                $today_slots[$time] = $time;
            }
        }
        return $today_slots;
    }

    public function cancelJobReasons()
    {
        return response()->json(['reasons' => config('constants.JOB_CANCEL_REASONS_FROM_CUSTOMER'), 'code' => 200]);
    }

    public function cancel($customer, $job, Request $request)
    {
        try {
            $job = Job::find($job);
            $previous_status = $job->status;
            $customer = $request->customer;
            $job_status = new JobStatus($job, $request);
            $job_status->__set('updated_by', $request->customer);
            if ($response = $job_status->update('Cancelled')) {
                $job_cancel_log = new JobCancelLogRepository($job);
                $job_cancel_log->__set('created_by', $customer);
                $job_cancel_log->store($previous_status, $request->reason);
                return api_response($request, true, 200);
            } else {
                return api_response($request, $response, $response->code);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function saveFavorites($customer, $job, Request $request)
    {
        try {
            $job = $request->job;
            try {
                DB::transaction(function () use ($customer, $job) {
                    $favorite = new CustomerFavorite(['category_id' => $job->category, 'name' => $job->category->name, 'additional_info' => $job->additional_info]);
                    $customer->favorites()->save($favorite);
                    foreach ($job->jobServices as $jobService) {
                        $favorite->services()->attach($jobService->service_id, [
                            'name' => $jobService->service->name, 'variable_type' => $jobService->variable_type,
                            'variables' => $jobService->variable,
                            'option' => $jobService->option,
                            'quantity' => (double)$jobService->min_quantity
                        ]);
                    }
                });
                return api_response($request, 1, 200);
            } catch (QueryException $e) {
                return api_response($request, null, 500);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function clearBills($customer, $job, Request $request)
    {
        try {
            $this->validate($request, [
                'payment_method' => 'sometimes|required|in:online,bkash'
            ]);
            $order_adapter = new OrderAdapter($request->job->partnerOrder);
            $payment = (new PayCharge($request->has('payment_method') ? $request->payment_method : 'online'))->init($order_adapter->getPayable());
            return api_response($request, $payment, 200, ['link' => $payment['link'], 'payment' => $payment]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
