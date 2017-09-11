<?php

namespace App\Http\Middleware;

use App\Models\Resource;
use Closure;

class ResourceAuthMiddleware
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
        If ($request->has('remember_token')) {
            $resource = Resource::where('remember_token', $request->input('remember_token'))->first();
            if ($resource) {
                if ($resource->id == $request->resource) {
                    $request->merge(['resource' => $resource]);
                    return $next($request);
                }
            }
            return api_response($request, null, 403);
        }
        return api_response($request, null, 401);
    }
}
