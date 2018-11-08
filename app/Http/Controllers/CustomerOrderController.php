<?php

namespace App\Http\Controllers;


use App\Models\Job;
use App\Models\PartnerOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Logs\Customer\JobLogs;

class CustomerOrderController extends Controller
{
    public function index($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'filter' => 'sometimes|string|in:ongoing,history',
                'for' => 'sometimes|required|string|in:eshop'
            ]);
            $filter = $request->filter;
            $for = $request->for;
            list($offset, $limit) = calculatePagination($request);
            $customer = $request->customer->load(['orders' => function ($q) use ($filter, $offset, $limit, $for) {
                $q->select('id', 'customer_id', 'partner_id', 'location_id', 'sales_channel', 'delivery_name', 'delivery_mobile', 'delivery_address')->orderBy('id', 'desc')
                    ->skip($offset)->take($limit);
                if ($for == 'eshop') {
                    $q->whereNotNull('partner_id');
                } else {
                    $q->whereNull('partner_id');
                }
                if ($filter) {
                    $q->whereHas('partnerOrders', function ($q) use ($filter) {
                        $q->$filter();
                    });
                }
                $q->with(['partnerOrders' => function ($q) use ($filter, $offset, $limit) {
                    $q->with(['partner.resources.profile', 'order' => function ($q) {
                        $q->select('id', 'sales_channel', 'favorite_id');
                    }, 'jobs' => function ($q) {
                        $q->with(['statusChangeLogs', 'resource.profile', 'jobServices', 'customerComplains', 'category', 'review' => function ($q) {
                            $q->select('id', 'rating', 'job_id');
                        }, 'usedMaterials']);
                    }]);
                }]);
            }]);
            if (count($customer->orders) > 0) {
                $all_jobs = $this->getInformation($customer->orders);
                $cancelled_served_jobs = $all_jobs->filter(function ($job) {
                    return $job['cancelled_date'] != null || $job['status'] == 'Served';
                });
                $others = $all_jobs->diff($cancelled_served_jobs);
                $all_jobs = $others->merge($cancelled_served_jobs);
            } else {
                $all_jobs = collect();
            }
            return count($all_jobs) > 0 ? api_response($request, $all_jobs, 200, ['orders' => $all_jobs->values()->all()]) : api_response($request, null, 404);
        } catch ( ValidationException $e ) {
            app('sentry')->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch ( \Throwable $e ) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
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
            $final = collect();
            foreach ($partner_order->jobs as $job) {
                $final->push($this->getJobInformation($job, $partner_order));
            }
            removeRelationsAndFields($partner_order);
            $partner_order['jobs'] = $final;
            return api_response($request, $partner_order, 200, ['orders' => $partner_order]);
        } catch ( \Throwable $e ) {
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
            'category_name' => $category ? $category->name : null,
            'category_thumb' => $category ? $category->thumb : null,
            'schedule_date' => $job->schedule_date ? $job->schedule_date : null,
            'served_date' => $job->delivered_date ? $job->delivered_date->format('Y-m-d H:i:s') : null,
            'process_date' => $process_log ? $process_log->created_at->format('Y-m-d H:i:s') : null,
            'cancelled_date' => $partnerOrder->cancelled_at,
            'schedule_date_readable' => (Carbon::parse($job->schedule_date))->format('M j, Y'),
            'preferred_time' => $job->preferred_time ? humanReadableShebaTime($job->preferred_time) : null,
            'readable_status' => constants('JOB_STATUSES_SHOW')[$job->status]['customer'],
            'status' => $job->status,
            'is_on_premise' => (int) $job->isOnPremise(),
            'customer_favorite' => $partnerOrder->order->favorite_id ? : null,
            'isRentCar' => $job->isRentCar(),
            'status_color' => constants('JOB_STATUSES_COLOR')[$job->status]['customer'],
            'partner_name' => $partnerOrder->partner->name,
            'partner_logo' => $partnerOrder->partner->logo,
            'resource_name' => $job->resource ? $job->resource->profile->name : null,
            'resource_pic' => $job->resource ? $job->resource->profile->pro_pic : null,
            'contact_number' => $show_expert ? ($job->resource ? $job->resource->profile->mobile : null) : $partnerOrder->partner->getManagerMobile(),
            'contact_person' => $show_expert ? 'expert' : 'partner',
            'rating' => $job->review != null ? $job->review->rating : null,
            'price' => (double)$partnerOrder->totalPrice,
            'order_code' => $partnerOrder->order->code(),
            'created_at' => $partnerOrder->created_at->format('Y-m-d'),
            'created_at_timestamp' => $partnerOrder->created_at->timestamp,
            'version' => $partnerOrder->getVersion(),
            'original_price' => (double)$partnerOrder->jobPrices,
            'discount' => (double)$partnerOrder->totalDiscount,
            'discounted_price' => (double)$partnerOrder->totalPrice,
            'complain_count' => $job->customerComplains->count(),
            'message' => (new JobLogs($job))->getOrderMessage()
        ));
    }
}