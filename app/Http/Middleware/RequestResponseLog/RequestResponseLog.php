<?php

namespace App\Http\Middleware\RequestResponseLog;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Closure;

abstract class RequestResponseLog
{
    protected $method;
    protected $full_url;
    protected $host;
    protected $response_time;
    protected $req;
    protected $res;

    public function handle($request, Closure $next)
    {
        $response = $next($request);
        // Add response time as an HTTP header. For better accuracy ensure this middleware
        if (defined('LARAVEL_START') and $response instanceof Response) {
            $response->headers->add(['X-RESPONSE-TIME' => microtime(true) - LARAVEL_START]);
        }

        return $response;
    }

    public function terminate($request, $response)
    {
        if (defined('LARAVEL_START') and $request instanceof Request) {
            $this->method = $request->getMethod();
            $this->full_url = $request->fullUrl();
            $this->host = $request->getHost();
            $this->response_time = microtime(true) - LARAVEL_START;
            $this->req = $this->getRequest($request);
            $this->res = $response->getContent();

            $this->logEntry();
        }
    }

    abstract protected function getRequest($request);

    abstract protected function logEntry();
}