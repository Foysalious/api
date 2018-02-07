<?php

namespace App\Http\Middleware;


use App\Models\Job;
use Closure;

class CustomerJobAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        $customer = $request->customer;
        $job = Job::with('partner_order.order')->find((int)$request->job);
        if (!$job) {
            return api_response($request, null, 404);
        }
        if ($job->partner_order->order->customer_id != $customer->id) {
            return api_response($request, null, 403);
        }
        $request->merge(['job' => $job]);
        return $next($request);
    }
}