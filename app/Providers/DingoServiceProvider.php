<?php namespace App\Providers;

use Dingo\Api\Provider\LaravelServiceProvider;
use App\Exceptions\CustomHandler as ExceptionHandler;
use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandler;

class DingoServiceProvider extends LaravelServiceProvider
{
    protected function registerExceptionHandler()
    {
        $this->app->singleton('api.exception', function ($app) {
            $handler = $app[IlluminateExceptionHandler::class];
            return new ExceptionHandler($handler, $this->config('errorFormat'), $this->config('debug'));
        });
    }
}
