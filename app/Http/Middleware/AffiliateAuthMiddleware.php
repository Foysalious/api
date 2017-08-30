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
        If ($request->has('remember_token')) {
            $affiliate = Affiliate::where('remember_token', $request->input('remember_token'))->first();
            if ($affiliate) {
                if ($affiliate->id == $request->affiliate) {
                    $request->merge(['affiliate' => $affiliate]);
                    return $next($request);
                }
            }
            return api_response($request, null, 403);
        }
        return api_response($request, null, 401);
    }
}
