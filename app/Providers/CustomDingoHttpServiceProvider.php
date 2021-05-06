<?php namespace App\Providers;


use Dingo\Api\Auth\Auth;
use Dingo\Api\Contract\Debug\ExceptionHandler;
use Dingo\Api\Http\Middleware\Auth as AuthMiddleware;
use Dingo\Api\Http\Middleware\PrepareController;
use Dingo\Api\Http\Middleware\RateLimit;
use Dingo\Api\Http\Middleware\Request;
use Dingo\Api\Http\RateLimit\Handler;
use Dingo\Api\Http\RequestValidator;
use Dingo\Api\Provider\HttpServiceProvider;
use Dingo\Api\Routing\Router;

class CustomDingoHttpServiceProvider extends HttpServiceProvider
{
    /**
     * Register the middleware.
     *
     * @return void
     */
    protected function registerMiddleware()
    {
        $this->app->singleton(Request::class, function ($app) {
            $middleware = new CustomDingoRequestMiddleware(
                $app,
                $app[ExceptionHandler::class],
                $app[Router::class],
                $app[RequestValidator::class],
                $app['events']
            );

            $middleware->setMiddlewares($this->config('middleware', false));

            return $middleware;
        });

        $this->app->singleton(AuthMiddleware::class, function ($app) {
            return new AuthMiddleware($app[Router::class], $app[Auth::class]);
        });

        $this->app->singleton(RateLimit::class, function ($app) {
            return new RateLimit($app[Router::class], $app[Handler::class]);
        });

        $this->app->singleton(PrepareController::class, function ($app) {
            return new PrepareController($app[Router::class]);
        });
    }
}
