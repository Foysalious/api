<?php namespace App\Providers;

use Dingo\Api\Provider\LaravelServiceProvider;
use App\Exceptions\CustomHandler as ExceptionHandler;
use Dingo\Api\Provider\RoutingServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler as IlluminateExceptionHandler;

class DingoServiceProvider extends LaravelServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();

        $this->registerClassAliases();

        $this->app->register(RoutingServiceProvider::class);

        $this->app->register(CustomDingoHttpServiceProvider::class);

        $this->registerExceptionHandler();

        $this->registerDispatcher();

        $this->registerAuth();

        $this->registerTransformer();

        $this->registerDocsCommand();

        if (class_exists('Illuminate\Foundation\Application', false)) {
            $this->commands([
                \Dingo\Api\Console\Command\Cache::class,
                \Dingo\Api\Console\Command\Routes::class,
            ]);
        }
    }

    protected function registerExceptionHandler()
    {
        $this->app->singleton('api.exception', function ($app) {
            $handler = $app[IlluminateExceptionHandler::class];
            return new ExceptionHandler($handler, $this->config('errorFormat'), $this->config('debug'));
        });
    }
}
