<?php namespace Sheba\Analysis\PartnerPerformance\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

use Sheba\Analysis\PartnerPerformance\PartnerPerformance;
use Sheba\Analysis\PartnerPerformance\Calculators\Basic;
use Sheba\Analysis\PartnerPerformance\Calculators\CacheWrapper;
use Sheba\Analysis\PartnerPerformance\Calculators\StatDbWrapper;

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
            return new Basic();
            //return new CacheWrapper(new StatDbWrapper(new Basic()));
        });
    }
}