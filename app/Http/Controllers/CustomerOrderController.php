<?php

namespace App\Http\Controllers;


use App\Models\PartnerOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerOrderController extends Controller
{
    public function index($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'filter' => 'required|string|in:ongoing,history'
            ]);
            $filter = $request->filter;
            $customer = $request->customer->load(['partnerOrders' => function ($q) use ($filter) {
                $q->$filter()->with(['partner', 'order', 'jobs' => function ($q) {
                    $q->with(['resource.profile', 'category', 'review']);
                }]);
            }]);
            $all_jobs = $this->getInformation($customer->partnerOrders)->sortByDesc('created_at');
            return count($all_jobs) > 0 ? api_response($request, $all_jobs, 200, ['orders' => $all_jobs->values()->all()]) : api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function getInformation($partnerOrders)
    {
        $all_jobs = collect();
        foreach ($partnerOrders as $partnerOrder) {
            $partnerOrder->calculate(true);
            $job = $partnerOrder->jobs[0];
            $all_jobs->push($this->getJobInformation($job, $partnerOrder));
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
            return api_response($request, null, 500);
        }
    }

    private function getJobInformation($job, $partnerOrder)
    {
        $category = $job->category;
        return collect(array(
            'id' => $partnerOrder->id,
            'job_id' => $job->id,
            'category_name' => $category ? $category->name : null,
            'category_thumb' => $category ? $category->thumb : null,
            'schedule_date' => $job->schedule_date ? $job->schedule_date : null,
            'preferred_time' => $job->preferred_time ? $job->preferred_time : null,
            'status' => $job->status,
            'status_color' => constants('JOB_STATUSES_COLOR')[$job->status]['customer'],
            'partner_name' => $partnerOrder->partner->name,
            'rating' => $job->review != null ? $job->review->rating : null,
            'price' => (double)$job->totalPrice,
            'order_code' => $partnerOrder->order->code(),
            'created_at' => $job->created_at->format('Y-m-d'),
            'created_at_timestamp' => $job->created_at->timestamp,
            'version' => $partnerOrder->getVersion()
        ));
    }
}