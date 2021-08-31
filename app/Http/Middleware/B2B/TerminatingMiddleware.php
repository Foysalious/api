<?php namespace App\Http\Middleware\B2B;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Log;

class TerminatingMiddleware
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
        // Add response time as an HTTP header. For better accuracy ensure this middleware
        if (defined('LARAVEL_START') and $response instanceof Response) {
            $response->headers->add(['X-RESPONSE-TIME' => microtime(true) - LARAVEL_START]);
        }

        return $response;
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    public function terminate($request, $response)
    {
        if (defined('LARAVEL_START') and $request instanceof Request) {
            $method = $request->getMethod();
            $url = $request->url();
            $full_url = $request->fullUrl();
            $port = $request->getPort();
            $host = $request->getHost();
            $response_time = microtime(true) - LARAVEL_START;
            $req = $request->getContent();
            $res = $response->getContent();

            app('log')->debug("'Response time', 'method': $method, 'url': $url, 'host': $host, 'full_url': $full_url, 'port': $port, 'response_time': $response_time, 'req': $req, 'res': $res");
        }
    }
}