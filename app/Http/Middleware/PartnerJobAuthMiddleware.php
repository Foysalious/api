<?php namespace App\Http\Middleware;

use App\Models\Job;
use Closure;

class PartnerJobAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $partner = $request->partner;
        $job = Job::select('id', 'category_id', 'status', 'resource_id', 'partner_order_id', 'crm_id', 'preferred_time_start', 'preferred_time_end',
            'preferred_time', 'schedule_date')->where('id', $request->job)->first();
        if (!$job) {
            return api_response($request, null, 404, ["message" => "Order not found."]);
        }
        if ($job->partner_order->partner->id != $partner->id) {
            return api_response($request, null, 403, ["message" => "You're not authorized to access this order."]);
        }
        $request->merge(['job' => $job]);
        return $next($request);
    }
}
