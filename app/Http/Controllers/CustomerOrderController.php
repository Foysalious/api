<?php

namespace App\Http\Controllers;


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
            $all_jobs = $this->getJobOfOrders($customer->partnerOrders)->sortByDesc('created_at');
            return count($all_jobs) > 0 ? api_response($request, $all_jobs, 200, ['orders' => $all_jobs->values()->all()]) : api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            return api_response($request, null, 500);
        }
    }

    private function getJobOfOrders($partnerOrders)
    {
        $all_jobs = collect();
        foreach ($partnerOrders as $partnerOrder) {
            $partnerOrder->calculate(true);
            $job = $partnerOrder->jobs[0];
            $category = $job->category;
            $all_jobs->push(collect(array(
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
                'order_code' => $partnerOrder->order->code(),
                'created_at' => $job->created_at->format('Y-m-d'),
                'created_at_timestamp' => $job->created_at->timestamp
            )));
        }
        return $all_jobs;
    }
}