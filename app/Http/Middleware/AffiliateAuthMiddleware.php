<?php

namespace App\Http\Middleware;

use App\Models\Affiliate;
use Closure;

class AffiliateAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        If ($request->has('remember_token')) {
            $affiliate = Affiliate::where('remember_token', $request->input('remember_token'))->first();
            //remember_token is valid for a customer
            if ($affiliate) {
                if ($affiliate->id == $request->affiliate) {
                    return $next($request);
                } else {
                    return response()->json(['msg' => 'unauthorized', 'code' => 409]);
                }
            }
        }
        return response()->json(['msg' => 'unauthorized', 'code' => 409]);
    }
}
