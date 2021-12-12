<?php

namespace App\Http\Middleware\Sheba;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ShebaNetworkMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return in_array($request->ip(), ['127.0.0.1','172.19.0.4', '172.19.0.1', '172.31.27.35', '13.232.181.83',"172.20.0.6","172.25.0.2"]) ? $next($request) : response()->json(
            ['message' => 'unauthorized', 'code' => 409]);
    }
}
