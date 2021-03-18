<?php namespace App\Http\Middleware\B2B;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Redis;

class TerminatingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param  Request  $request
     * @param  Response  $response
     * @return void
     */
    public function terminate($request, $response)
    {
        $key = 'BusinessRequestResponse::'.Carbon::now()->timestamp;
        Redis::set($key, json_encode('Request_Uri:'. $request->getUri(), 'Request_All:'. json_encode($request->all()), '_Response:' .json_encode($response)));
    }
}