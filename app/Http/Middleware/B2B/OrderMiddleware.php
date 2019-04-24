<?php


namespace App\Http\Middleware\B2B;


use App\Models\Customer;
use App\Models\Job;
use Closure;

class OrderMiddleware
{
    public function handle($request, Closure $next)
    {
        $customer =Customer::find(11);
        $job = Job::with('partnerOrder.order')->find((int)$request->order);
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