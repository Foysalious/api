<?php

namespace App\Http\Middleware;


use App\Models\Job;
use Closure;

class CustomerJobAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $customer = $request->customer;
        $job = Job::with('partnerOrder.order')->find((int)$request->job);
        if (!$job) {
            return api_response($request, null, 404, ["message" => "Order not found."]);
        }
        if ($job->partnerOrder->order->customer_id != $customer->id) {
            return api_response($request, null, 403, ["message" => "You're not authorized to access this order."]);
        }
        $request->merge(['job' => $job]);
        return $next($request);
    }
}