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
        if ($request->filled('remember_token')) {
            $resource = Resource::where('remember_token', $request->input('remember_token'))->first();
            if ($resource) {
                if ($resource->id == (int)$request->resource) {
                    $request->merge(['resource' => $resource]);
                    return $next($request);
                } else {
                    return api_response($request, null, 403, ["message" => "You're not authorized to access this user."]);
                }
            } else {
                return api_response($request, null, 404, ["message" => "User not found."]);
            }
        } else {
            return api_response($request, null, 400, ["message" => "Authentication token is missing from the request."]);
        }
    }
}
