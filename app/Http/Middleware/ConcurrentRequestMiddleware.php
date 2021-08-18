<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;

class ConcurrentRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $paramName, $duration=1, $maxHit=1)
    {
        return $next($request);
//        $rateLimiter = app(RateLimiter::class);
//        $key = $paramName . '-' .$request->route($paramName)->id;
//        if($rateLimiter->retriesLeft($key, $maxHit) <= 0) {
//            return response()->json(['code' => 403, 'message' => 'You need to wait at least ' . $duration . 'minutes before requesting again.']);
//        }
//        $rateLimiter->hit($key, $duration);
//        return $next($request);
    }
}
