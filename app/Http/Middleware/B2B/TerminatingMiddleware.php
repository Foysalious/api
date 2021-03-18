<?php namespace App\Http\Middleware\B2B;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Closure;

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
        // ...
    }
}