<?php namespace Sheba\Analysis\PartnerSale\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

use Sheba\Analysis\PartnerSale\Calculators\Basic;
use Sheba\Analysis\PartnerSale\Calculators\CacheWrapper;
use Sheba\Analysis\PartnerSale\Calculators\StatDbWrapper;
use Sheba\Analysis\PartnerSale\PartnerSale;

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
        $this->app->singleton(PartnerSale::class, function ($app) {
            return new Basic();
            //return new CacheWrapper(new StatDbWrapper(new Basic()));
        });
    }
}