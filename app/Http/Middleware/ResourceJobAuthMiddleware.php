<?php namespace App\Http\Middleware;

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
        $job = Job::where('id', $request->job)->first();
        if (!$job) {
            return api_response($request, null, 404, ["message" => "Order not found."]);
        }
        if ($job->resource_id != $resource->id) {
            return api_response($request, null, 403, ["message" => "You're not authorized to access this order."]);
        }
        $request->merge(['job' => $job]);
        return $next($request);
    }
}
