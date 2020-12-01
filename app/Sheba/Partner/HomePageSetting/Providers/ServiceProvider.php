<?php namespace Sheba\Partner\HomePageSetting\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

use Sheba\Partner\HomePageSetting\CacheManager;
use Sheba\Partner\HomePageSetting\Calculators\Basic;
use Sheba\Partner\HomePageSetting\Calculators\CacheWrapper;
use Sheba\Partner\HomePageSetting\Calculators\StatDbWrapper;

use Sheba\Partner\HomePageSetting\Setting;

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
        $this->app->singleton(Setting::class, function ($app) {
            return (new CacheWrapper(new StatDbWrapper(new Basic())));
        });
    }
}
