<?php

namespace App\Http\Controllers;


use App\Models\Job;
use App\Models\PartnerOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerOrderController extends Controller
{
    public function index($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'filter' => 'sometimes|string|in:ongoing,history'
            ]);
            $filter = $request->filter;
            list($offset, $limit) = calculatePagination($request);
            $customer = $request->customer->load(['partnerOrders' => function ($q) use ($filter, $offset, $limit) {
                if ($filter) $q->$filter();
                $q->orderBy('id', 'desc')->skip($offset)->take($limit)->with(['partner.resources.profile', 'order' => function ($q) {
                    $q->select('id', 'sales_channel');
                }, 'jobs' => function ($q) {
                    $q->with(['resource.profile', 'jobServices', 'customerComplains', 'category' => function ($q) {
                        $q->select('id', 'name');
                    }, 'review' => function ($q) {
                        $q->select('id', 'rating');
                    }, 'usedMaterials']);
                }]);
            }]);
            $all_jobs = $this->getInformation($customer->partnerOrders)->sortByDesc('id');
            return count($all_jobs) > 0 ? api_response($request, $all_jobs, 200, ['orders' => $all_jobs->values()->all()]) : api_response($request, null, 404);
        } catch (ValidationException $e) {
            app('sentry')->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getInformation($partnerOrders)
    {
        $all_jobs = collect();
        foreach ($partnerOrders as $partnerOrder) {
            $partnerOrder->calculate(true);
            $job = ($partnerOrder->jobs->filter(function ($job) {
                return $job->status !== 'Cancelled';
            }))->first();
            if ($job != null) {
                $all_jobs->push($this->getJobInformation($job, $partnerOrder));
            }
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getJobInformation(Job $job, PartnerOrder $partnerOrder)
    {
        $category = $job->category;
        $show_expert = $job->canCallExpert();
        return collect(array(
            'id' => $partnerOrder->id,
            'job_id' => $job->id,
            'category_name' => $category ? $category->name : null,
            'category_thumb' => $category ? $category->thumb : null,
            'schedule_date' => $job->schedule_date ? $job->schedule_date : null,
            'schedule_date_readable' => (Carbon::parse($job->schedule_date))->format('jS F, Y'),
            'preferred_time' => $job->preferred_time ? humanReadableShebaTime($job->preferred_time) : null,
            'readable_status' => constants('JOB_STATUSES_SHOW')[$job->status]['customer'],
            'status' => $job->status,
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
            'cancelled_at' => $partnerOrder->cancelled_at ? $partnerOrder->cancelled_at->format('Y-m-d') : null,
            'created_at' => $partnerOrder->created_at->format('Y-m-d'),
            'created_at_timestamp' => $partnerOrder->created_at->timestamp,
            'version' => $partnerOrder->getVersion(),
            'original_price' => (double)$partnerOrder->totalServicePrice,
            'discount' => (double)$partnerOrder->totalDiscount,
            'discounted_price' => (double)$partnerOrder->totalPrice,
            'complain_count' => $job->customerComplains->count()
        ));
    }
}