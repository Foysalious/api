<?php

namespace App\Http\Middleware;

use App\Models\Partner;
use App\Models\Resource;
use Closure;

class ManagerAuthMiddleware
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
        if ($request->has('remember_token')) {
            $resource = Resource::where('remember_token', $request->input('remember_token'))->first();
            $partner = Partner::find($request->partner);
            if ($resource && $partner) {
                if ($resource->isManager($partner)) {
                    $request->merge(['resource' => $resource, 'partner' => $partner]);
                    return $next($request);
                }
            }
            return api_response($request, null, 403);
        }
        return api_response($request, null, 401);
    }
}
