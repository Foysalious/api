<?php

namespace App\Http\Middleware;

use App\Models\Job;
use Closure;

class ResourceJobAuthMiddleware
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
        $resource = $request->resource;
        $job = Job::select('id', 'status', 'resource_id', 'partner_order_id')->where('id', $request->job)->first();
        if (!$job) {
            return api_response($request, null, 404);
        }
        if ($job->resource_id != $resource->id) {
            return api_response($request, null, 403);
        }
        $request->merge(['job' => $job]);
        return $next($request);
    }
}
