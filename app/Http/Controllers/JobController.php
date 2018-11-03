<?php

namespace App\Http\Controllers;

use App\Models\CustomerFavorite;
use App\Models\Job;
use App\Models\JobCancelReason;
use App\Models\Partner;
use App\Models\Resource;
use App\Repositories\JobCancelLogRepository;
use App\Sheba\JobStatus;
use App\Sheba\UserRequestInformation;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Sheba\Logs\Customer\JobLogs;
use Sheba\Payment\Adapters\Payable\OrderAdapter;
use Sheba\Payment\ShebaPayment;

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
            $job_collection->put('category_id', $job->category ? $job->category->id : null);
            $job_collection->put('category_name', $job->category ? $job->category->name : null);
            $job_collection->put('partner_name', $job->partnerOrder->partner->name);
            $job_collection->put('status', $job->status);
            $job_collection->put('rating', $job->review ? $job->review->rating : null);
            $job_collection->put('review', $job->review ? $job->review->calculated_review : null);
            $job_collection->put('original_price', (double)$job->partnerOrder->jobPrices);
            $job_collection->put('discount', (double)$job->partnerOrder->totalDiscount);
            $job_collection->put('payment_method', $this->formatPaymentMethod($job->partnerOrder->payment_method));
            $job_collection->put('price', (double)$job->partnerOrder->totalPrice);
            $job_collection->put('isDue', (double)$job->partnerOrder->due > 0 ? 1 : 0);
            $job_collection->put('isRentCar', $job->isRentCar());
            $job_collection->put('is_on_premise', $job->isOnPremise());
            $job_collection->put('partner_address', $job->partnerOrder->partner->address);
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
                $services->push(
                    array(
                        'service_id' => $job->service->id,
                        'name' => $job->service_name,
                        'variables' => $variables,
                        'quantity' => $job->service_quantity,
                        'unit' => $job->service->unit,
                        'option' => $job->service_option,
                        'variable_type' => $job->service_variable_type,
                        'thumb' => $job->service->app_thumb
                    )
                );
            } else {
                $services = collect();
                foreach ($job->jobServices as $jobService) {
                    $variables = json_decode($jobService->variables);
                    $services->push(
                        array(
                            'service_id' => $jobService->service->id,
                            'name' => $jobService->formatServiceName($job),
                            'variables' => $variables,
                            'unit' => $jobService->service->unit,
                            'quantity' => $jobService->quantity,
                            'option' => $jobService->option,
                            'variable_type' => $jobService->variable_type,
                            'thumb' => $jobService->service->app_thumb
                        )
                    );
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
                        'quantity' => $jobService->quantity,
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
            $bill['original_price'] = (double)$partnerOrder->jobPrices;
            $bill['paid'] = (double)$partnerOrder->paid;
            $bill['due'] = (double)$partnerOrder->due;
            $bill['material_price'] = (double)$job->materialPrice;
            $bill['discount'] = (double)$job->discount;
            $bill['services'] = $services;
            $bill['delivered_date'] = $job->delivered_date != null ? $job->delivered_date->format('Y-m-d') : null;
            $bill['delivered_date_timestamp'] = $job->delivered_date != null ? $job->delivered_date->timestamp : null;
            $bill['closed_and_paid_at'] = $partnerOrder->closed_and_paid_at ? $partnerOrder->closed_and_paid_at->format('Y-m-d') : null;
            $bill['closed_and_paid_at_timestamp'] = $partnerOrder->closed_and_paid_at != null ? $partnerOrder->closed_and_paid_at->timestamp : null;
            $bill['payment_method'] = $this->formatPaymentMethod($partnerOrder->payment_method);
            $bill['status'] = $job->status;
            $bill['is_on_premise'] = (int)$job->isOnPremise();
            $bill['delivery_charge'] = (double)$partnerOrder->deliveryCharge;
            $bill['invoice'] = $job->partnerOrder->invoice;
            $bill['version'] = $job->partnerOrder->getVersion();
            return api_response($request, $bill, 200, ['bill' => $bill]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function formatPaymentMethod($payment_method)
    {
        if ($payment_method == 'Cash On Delivery' ||
            $payment_method == 'cash-on-delivery') return 'cod';
        return strtolower($payment_method);
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
                        'created_at_timestamp' => $job->created_at->timestamp,
                        'message' => (new JobLogs($job))->getOrderMessage()
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
            $this->validate($request, [
                'remember_token' => 'required',
                'cancel_reason' => 'required|exists:job_cancel_reasons,key,is_published_for_customer,1',
                'cancel_reason_details' => 'sometimes|string'
            ]);

            $client = new Client();
            $res = $client->request('POST', env('SHEBA_BACKEND_URL') . '/api/job/' . $job . '/change-status',
                [
                    'form_params' => array_merge((new UserRequestInformation($request))->getInformationArray(), [
                        'customer_id' => $customer,
                        'remember_token' => $request->remember_token,
                        'status' => constants('JOB_STATUSES')['Cancelled'],
                        'cancel_reason' => $request->cancel_reason,
                        'cancel_reason_details' => $request->cancel_reason_details,
                        'created_by_type' => get_class($request->customer)
                    ])
                ]);
            if ($response = json_decode($res->getBody())) {
                return api_response($request, $response, $response->code);
            }
            return api_response($request, null, 500);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (RequestException $e) {
            app('sentry')->captureException($e);
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
                'payment_method' => 'sometimes|required|in:online,wallet,bkash,cbl'
            ]);
            $order_adapter = new OrderAdapter($request->job->partnerOrder);
            $payment = (new ShebaPayment($request->has('payment_method') ? $request->payment_method : 'online'))->init($order_adapter->getPayable());
            return api_response($request, $payment, 200, ['link' => $payment->redirect_url, 'payment' => $payment->getFormattedPayment()]);
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

    public function getOrderLogs($customer, Request $request)
    {
        try {
            $job = $request->job;
            $logs = (new JobLogs($job))->getorderStatusLogs();
            return api_response($request, $logs, 200, ['logs' => $logs]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getFaqs(Request $request)
    {
        try {
            $this->validate($request, [
                'status' => 'required'
            ]);
            $status = strtolower($request->status);
            $faqs = [];
            if (in_array($status, ['pending', 'not responded', 'declined'])) {
                $faqs = array(
                    array(
                        'question' => 'When my order will be confirmed?',
                        'answer' => 'When you placed an order, service provider will be notified. It will take 5-10 minutes for Service provider to accept the order. You can also call service provider to confirm.'
                    ),
                    array(
                        'question' => 'What if Service Provider declined my order?',
                        'answer' => 'If service provider declined to serve your order at that moment, Sheba.xyz will notify you and assign a new suitable service provider for you.'
                    ),
                    array(
                        'question' => 'How can I change service provider?',
                        'answer' => 'You can’t change service provider after confirming by service provider. In this case you can call 16516 or directly chat with us for help.'
                    ),
                    array(
                        'question' => 'Who will come to work on my order?',
                        'answer' => 'After confirming the order, Service provider will assign an expert for the order. Expert will come to work on your order on schedule time and date.'
                    ),
                    array(
                        'question' => 'What if I used the wrong payment method?',
                        'answer' => 'Unfortunately, you can’t change payment methods after placing the order. For more information, directly chat with us.'
                    ),
                    array(
                        'question' => 'What if my service provider or expert aren’t receiving my call?',
                        'answer' => 'If you failed to catch service provider or expert by several call, you can create an issue or chat with us. '
                    )
                );
            } elseif (in_array($status, ['accepted', 'schedule due', 'process', 'serve due'])) {
                $faqs = array(
                    array(
                        'question' => 'What if I want to reschedule the order?',
                        'answer' => 'You can reschedule your order by calling service provider. You can check your new schedule time and date from order details page.'
                    ),
                    array(
                        'question' => 'What if I pay advance to Service Provider?',
                        'answer' => 'If you pay in advance to service provider, bill section will be updated. You can check the bill section to know in details about the bill.'
                    ),
                    array(
                        'question' => 'What if I used the wrong payment method?',
                        'answer' => 'Unfortunately, you can’t change payment methods after placing the order. For more information, directly chat with us.'
                    ),
                    array(
                        'question' => 'Who will come to work on my order?',
                        'answer' => 'After confirming the order, Service provider will assign an expert for the order. Expert will come to work on your order on schedule time and date.'
                    ),
                    array(
                        'question' => 'What if my order is not started in schedule time?',
                        'answer' => 'You will get a notification 30 minutes before your selected schedule slots. If your order hasn’t started in time, you can call expert to know the issue or you can let Sheba.xyz know by creating an issue.'
                    ),
                    array(
                        'question' => 'What if my service provider or expert isn’t receiving my call?',
                        'answer' => 'If you failed to catch service provider or expert by several call, you can create an issue or chat with us.'
                    ),
                );
            } elseif (in_array($status, ['served'])) {
                $faqs = array(
                    array(
                        'question' => 'What if expert asks for additional payment?',
                        'answer' => 'Expert or Service Provider should not ask for extra payment. You don’t need to pay any additional payment. Tips are not also expected or required. If you wish to tip, the adjustment to the total bill will not be made. If expert asks for any additional payment which is not found in app, create an issue or directly chat with us'
                    ),
                    array(
                        'question' => 'What if I want to create an issue against my service provider and expert?',
                        'answer' => 'You can create an issue by clicking ‘Get Support’ option from the order details page. Sheba.xyz support team will receive the issue and solve it within 72 hours.'
                    ),
                    array(
                        'question' => 'What can I do if I am not satisfied with the service quality?',
                        'answer' => 'You can rate your experience so that service provider will take action against the expert or you can create an issue from ‘Get Support’ option'
                    )
                );
            } elseif (in_array($status, ['cancelled'])) {
                $faqs = array(
                    array(
                        'question' => 'What if my order is mistakenly cancelled?',
                        'answer' => 'In this case, you can directly chat with us informing about the issue. Our support management team will look after the issue.'
                    ),
                    array(
                        'question' => 'What if someone asks me for cancellation fee?',
                        'answer' => 'Currently we don’t have any cancellation fee. If any expert or service provider ask you for the cancellation fee, kindly message us from message section of the app.'
                    ),
                    array(
                        'question' => 'What if I paid online and my order is cancelled?',
                        'answer' => 'Our Support management team will look after this issue. They will investigate and refund at your account within 72 working hours.'
                    )
                );
            }
            return api_response($request, $faqs, 200, ['faqs' => $faqs]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function cancelReason(Request $request)
    {
        try {
            $job_cancel_reasons = JobCancelReason::ForCustomer()->select('id', 'name', 'key')->get();
            return api_response($request, $job_cancel_reasons, 200, ['cancel-reason' => $job_cancel_reasons]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
