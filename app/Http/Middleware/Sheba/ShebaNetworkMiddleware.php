<?php namespace App\Http\Middleware\Sheba;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        return in_array($request->ip(), ['127.0.0.1', '172.22.0.1', '172.20.0.5', '172.20.0.7', '172.20.0.2', '172.31.27.35', '13.232.181.83', '103.197.207.30', "172.18.0.1", "172.20.0.6"]) ?
            $next($request) :
            response()->json(['message' => 'unauthorized', 'code' => 409]);
    }
}
