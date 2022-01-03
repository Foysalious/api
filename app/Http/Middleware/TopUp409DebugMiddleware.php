<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TopUp409DebugMiddleware
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
        $response = $next($request);

        if (!($response instanceof JsonResponse)) return $response;

        if ($response->getData()->code != 409) return $response;

        $req = json_encode($request->all());
        $head = json_encode($request->header());
        $res = json_encode($response->getData());
        Log::debug("$req $head $res");

        return $response;
    }
}