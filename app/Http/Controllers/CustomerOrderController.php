<?php

namespace App\Http\Controllers;


use App\Models\Job;
use App\Models\PartnerOrder;
use App\Repositories\ReviewRepository;
use App\Transformers\Customer\CustomerDueOrdersTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Sheba\Logs\Customer\JobLogs;
use Throwable;

class CustomerOrderController extends Controller
{
    public function index($customer, Request $request)
    {
        try {
            ini_set('memory_limit', '2048M');
            $this->validate($request, [
                'filter' => 'sometimes|string|in:ongoing,history',
                'for' => 'sometimes|required|string|in:eshop,business',
                'status' => 'sometimes|string',
                'search' => 'sometimes|string'
            ]);
            $filter = $request->filter;
            $for = $request->for;
            $status = $request->status;
            $search = $request->search;
            list($offset, $limit) = calculatePagination($request);
            $customer = $request->customer->load(['orders' => function ($q) use ($filter, $offset, $limit, $for, $status, $search) {
                $q->select('id', 'customer_id', 'partner_id', 'location_id', 'sales_channel', 'delivery_name', 'delivery_mobile', 'delivery_address', 'subscription_order_id')->orderBy('id', 'desc');
                if (!$search && !$status) {
                    $q->skip($offset)->take($limit);
                }
                if ($for == 'eshop') {
                    $q->whereNotNull('partner_id');
                } else if ($for == "business") {
                    $q->whereNotNull('business_id');
                } else {
                    $q->whereNull('partner_id');
                }
                if ($filter) {
                    $q->whereHas('partnerOrders', function ($q) use ($filter) {
                        $q->$filter();
                    });
                }

                $q->with(['partnerOrders' => function ($q) use ($filter, $status) {
                    $q->with(['partner.resources.profile', 'order' => function ($q) use ($status) {
                        $q->select('id', 'sales_channel', 'subscription_order_id');
                    }, 'jobs' => function ($q) {
                        $q->with(['statusChangeLogs', 'resource.profile', 'jobServices', 'customerComplains', 'category', 'review' => function ($q) {
                            $q->select('id', 'rating', 'job_id');
                        }, 'usedMaterials']);
                    }]);
                }]);
            }]);
            if (count($customer->orders) > 0) {
                $all_orders = $customer->orders;
                if($status) {
                    $all_orders = $all_orders->filter(function ($order, $key) use ($status) {
                        return $order->lastPartnerOrder() ? $order->lastPartnerOrder()->lastJob()->status === $status : false;
                    });
                }
                $all_jobs = $this->getInformation($all_orders);
                $cancelled_served_jobs = $all_jobs->filter(function ($job) {
                    return $job['cancelled_date'] != null || $job['status'] == 'Served';
                });
                $others = $all_jobs->diff($cancelled_served_jobs);
                $all_jobs = $others->merge($cancelled_served_jobs);

                $all_jobs->map(function ($job) {
                    $order_job = Job::find($job['job_id']);
                    $job['can_pay'] = $this->canPay($order_job);
                    $job['can_take_review'] = $this->canTakeReview($order_job);
                    return $job;
                });
            } else {
                $all_jobs = collect();
            }
            if ($search) {
                $all_jobs = $all_jobs->filter(function ($job) use ($search) {
                    return (false !== stristr($job['order_code'], $search) || false !== stristr($job['category_name'], $search));
                });
            }
            if ($search || $status) {
                $all_orders = $all_jobs->values()->splice($offset, $limit);
            } else {
                $all_orders = $all_jobs->values()->all();
            }
            return count($all_jobs) > 0 ? api_response($request, $all_jobs, 200, ['orders' => $all_orders]) : api_response($request, null, 404);
        } catch (ValidationException $e) {
            app('sentry')->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    protected function canTakeReview($job)
    {
        $review = $job->review;

        if (!is_null($review) && $review->rating > 0) {
            return false;
        } else if ($job->partnerOrder->closed_at) {
            $closed_date = Carbon::parse($job->partnerOrder->closed_at);
            $now = Carbon::now();
            $difference = $closed_date->diffInDays($now);

            return $difference < constants('CUSTOMER_REVIEW_OPEN_DAY_LIMIT');
        } else {
            return false;
        }
    }

    protected function canPay($job)
    {
        $due = $job->partnerOrder->calculate(true)->due;
        $status = $job->status;

        if (in_array($status, ['Declined', 'Cancelled']))
            return false;
        else {
            return $due > 0;
        }
    }

    private function getInformation($orders)
    {
        $all_jobs = collect();
        foreach ($orders as $order) {
            $partnerOrders = $order->partnerOrders;
            $cancelled_partnerOrders = $partnerOrders->filter(function ($o) {
                return $o->cancelled_at != null;
            })->sortByDesc('cancelled_at');
            $not_cancelled_partnerOrders = $partnerOrders->filter(function ($o) {
                return $o->cancelled_at == null;
            });
            $partnerOrder = $not_cancelled_partnerOrders->count() == 0 ? $cancelled_partnerOrders->first() : $not_cancelled_partnerOrders->first();
            $partnerOrder->calculate(true);
            if (!$partnerOrder->cancelled_at) {
                $job = ($partnerOrder->jobs->filter(function ($job) {
                    return $job->status !== 'Cancelled';
                }))->first();
            } else {
                $job = $partnerOrder->jobs->first();
            }
            if ($job != null) $all_jobs->push($this->getJobInformation($job, $partnerOrder));
        }

        return $all_jobs;
    }

    public function show($customer, $order, Request $request)
    {
        try {
            $partner_order = PartnerOrder::find($order);
            $partner_order->calculate(true);
            $partner_order['total_paid'] = (double)$partner_order->paid;
            $partner_order['total_due'] = (double)$partner_order->due;
            $partner_order['total_price'] = (double)$partner_order->totalPrice;
            $partner_order['delivery_name'] = $partner_order->order->delivery_name;
            $partner_order['delivery_mobile'] = $partner_order->order->delivery_mobile;
            $partner_order['delivery_address'] = $partner_order->order->delivery_address_id ? $partner_order->order->deliveryAddress->name : $partner_order->order->delivery_address;
            $final = collect();
            foreach ($partner_order->jobs as $job) {
                $final->push($this->getJobInformation($job, $partner_order));
            }
            removeRelationsAndFields($partner_order);
            $partner_order['jobs'] = $final;
            return api_response($request, $partner_order, 200, ['orders' => $partner_order]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getJobInformation(Job $job, PartnerOrder $partnerOrder)
    {
        $category = $job->category;
        $show_expert = $job->canCallExpert();
        $process_log = $job->statusChangeLogs->where('to_status', constants('JOB_STATUSES')['Process'])->first();
        return collect(array(
            'id' => $partnerOrder->id,
            'job_id' => $job->id,
            'subscription_order_id' => $partnerOrder->order->subscription_order_id,
            'category_name' => $category ? $category->name : null,
            'category_thumb' => $category ? $category->thumb : null,
            'schedule_date' => $job->schedule_date ? $job->schedule_date : null,
            'schedule_date_for_b2b' => $job->schedule_date ? (Carbon::parse($job->schedule_date))->format('d/m/y') : null,
            'served_date' => $job->delivered_date ? $job->delivered_date->format('Y-m-d H:i:s') : null,
            'process_date' => $process_log ? $process_log->created_at->format('Y-m-d H:i:s') : null,
            'cancelled_date' => $partnerOrder->cancelled_at ? $partnerOrder->cancelled_at->format('Y-m-d') : null,
            'schedule_date_readable' => (Carbon::parse($job->schedule_date))->format('M j, Y'),
            'preferred_time' => $job->preferred_time ? humanReadableShebaTime($job->preferred_time) : null,
            'readable_status' => constants('JOB_STATUSES_SHOW')[$job->status]['customer'],
            'status' => $job->status,
            'is_on_premise' => (int)$job->isOnPremise(),
            'customer_favorite' => !empty($job->customerFavorite) ? $job->customerFavorite->id : null,
            'isRentCar' => $job->isRentCar(),
            'status_color' => constants('JOB_STATUSES_COLOR')[$job->status]['customer'],
            'partner_name' => $partnerOrder->partner ? $partnerOrder->partner->name : null,
            'partner_logo' => $partnerOrder->partner ? $partnerOrder->partner->logo : null,
            'partner_mobile_number' => $partnerOrder->partner ? $partnerOrder->partner->getManagerMobile() : null,
            'partner_total_rating'=> $partnerOrder->partner ?  $partnerOrder->partner->reviews->count():null,
            'partner_avg_rating' => $partnerOrder->partner ?  (new ReviewRepository)->getAvgRating($partnerOrder->partner->reviews):null,
            'resource_name' => $job->resource ? $job->resource->profile->name : null,
            'resource_pic' => $job->resource ? $job->resource->profile->pro_pic : null,
            'resource_mobile_number' => $job->resource ? $job->resource->profile->mobile : null,
            'resource_total_rating' => $job->resource ? $job->resource->reviews->count(): null,
            'resource_avg_rating' => $job->resource ? (new ReviewRepository)->getAvgRating($job->resource->reviews): null,
            'contact_number' => $show_expert ? ($job->resource ? $job->resource->profile->mobile : null) : ($partnerOrder->partner ? $partnerOrder->partner->getManagerMobile() : null),
            'contact_person' => $show_expert ? 'expert' : 'partner',
            'rating' => $job->review != null ? $job->review->rating : null,
            'price' => $partnerOrder->getCustomerPayable(),
            'order_code' => $partnerOrder->order->code(),
            'created_at' => $partnerOrder->created_at->format('Y-m-d'),
            'created_at_timestamp' => $partnerOrder->created_at->timestamp,
            'version' => $partnerOrder->getVersion(),
            'original_price' => (double)$partnerOrder->jobPrices + $job->logistic_charge,
            'discount' => (double)$partnerOrder->totalDiscount,
            'discounted_price' => (double)$partnerOrder->totalPrice + $job->logistic_charge,
            'complain_count' => $job->customerComplains->count(),
            'message' => (new JobLogs($job))->getOrderMessage(),
        ));
    }

    public function dueOrders($customer, Request $request)
    {
        $orders = $request->customer->partnerOrders();
        $due_orders = $orders->where('closed_at', '<>', null)->where('closed_and_paid_at', null)->orderBy('closed_at', 'ASC')->limit(1)->get();
        if ($due_orders->isEmpty()) return api_response($request, null, 404, ['message' => 'No Due Order Found.']);
        $fractal = new Manager();
        $fractal->setSerializer(new CustomSerializer());
        $resource = new Collection($due_orders, new CustomerDueOrdersTransformer());
        $dueOrders = $fractal->createData($resource)->toArray()['data'];
        return api_response($request, $dueOrders, 200, ['due_orders' => $dueOrders]);
    }

}
