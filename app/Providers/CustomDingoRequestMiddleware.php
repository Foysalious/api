<?php namespace App\Providers;

use Dingo\Api\Http\Middleware\Request;
use Dingo\Api\Http\Request as HttpRequest;
use Dingo\Api\Http\Response;
use Illuminate\Pipeline\Pipeline;

class CustomDingoRequestMiddleware extends Request
{
    /**
     * Send the request through the Dingo router.
     *
     * @param \Dingo\Api\Http\Request $request
     *
     * @return \Dingo\Api\Http\Response
     */
    protected function sendRequestThroughRouter(HttpRequest $request): Response
    {
        $this->app->instance('request', $request);

        $middlewares = [];
        foreach ($this->middleware as $middleware) {
            if ($middleware != "Dingo\Api\Http\Middleware\Request") $middlewares[$middleware] = $middleware;
        }
        return (new Pipeline($this->app))->send($request)->through(array_keys($middlewares))->then(function ($request) {
            return $this->router->dispatch($request);
        });
    }
}
