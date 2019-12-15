<?php namespace Sheba\Recommendations\HighlyDemands\Categories;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Recommender::class, function ($app) {
            return new Basic();
            return new CacheWrapper(new Basic());
        });
    }
}
