<?php namespace Sheba\Partner\HomePageSettingV3\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Sheba\Partner\HomePageSettingV3\Calculators\Basic;
use Sheba\Partner\HomePageSettingV3\Calculators\StatDbWrapper;

use Sheba\Partner\HomePageSettingV3\SettingV3;

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
        $this->app->singleton(SettingV3::class, function ($app) {
            return (new StatDbWrapper(new Basic()));
        });
    }
}
