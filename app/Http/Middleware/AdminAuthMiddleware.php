<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;

class AdminAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return in_array($request->ip(), ['127.0.0.1']) ? $next($request) : response()->json(
            ['message' => 'unauthorized', 'code' => 409]);
    }
}
