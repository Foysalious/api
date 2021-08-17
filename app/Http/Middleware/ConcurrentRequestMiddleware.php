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
    public function handle($request, Closure $next, $duration)
    {
        $rateLimiter = app(RateLimiter::class);
        $key = $request->route('job')->id;
        if($rateLimiter->attempts($key) > 0) {
            return response()->json(['code' => 403, 'message' => 'Too many requests']);
        }
        $rateLimiter->hit($key, $duration);
        return $next($request);
    }
}
