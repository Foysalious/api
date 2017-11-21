<?php

namespace App\Http\Middleware;

use App\Models\Resource;
use Closure;

class PartnerResourceAuthMiddleware
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
        $resource = Resource::where('id', $request->route('resource'))->first();
        if (!$resource) {
            return api_response($request, null, 404);
        }
        if (count($resource->partners->where('id', $partner->id)) == 0) {
            return api_response($request, null, 403);
        }
        $request->merge(['resource' => $resource]);
        return $next($request);
    }
}
