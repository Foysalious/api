<?php namespace Sheba\PartnerOrder\ConcurrentUpdateRestriction;

use Illuminate\Support\ServiceProvider;

class CURServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'Sheba\PartnerOrder\ConcurrentUpdateRestriction\CURDataInterface',
            'Sheba\PartnerOrder\ConcurrentUpdateRestriction\CURRedisData'
        );

        $this->app->singleton('concurrentUpdateRestriction', CURHandler::class);
    }
}
