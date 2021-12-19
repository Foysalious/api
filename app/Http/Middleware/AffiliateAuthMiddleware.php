<?php

namespace App\Http\Middleware;

use App\Models\Affiliate;
use Closure;

class AffiliateAuthMiddleware
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
            $affiliate = Affiliate::where('remember_token', $request->input('remember_token'))->first();
            if ($affiliate) {
                if ($affiliate->id == $request->affiliate) {
                    $request->merge(['affiliate' => $affiliate]);
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
