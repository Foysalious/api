<?php namespace Sheba\AppSettings\HomePageSetting\Getters;

use Illuminate\Support\ServiceProvider;

class Provider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Getter::class, function ($app) {
            return $app->make(Mock::class);
        });
    }
}