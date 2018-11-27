<?php namespace Sheba\Analysis\PartnerPerformance\Calculator;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Sheba\Analysis\PartnerPerformance\PartnerPerformance;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(){}

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PartnerPerformance::class, function ($app) {
            return new CacheWrapper(new StatDbWrapper(new Basic()));
        });
    }
}