<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Sheba\Dal\Providers\CustomMigrationServiceProvider;
use Sheba\Voucher\VoucherCodeServiceProvider;
use Sheba\Voucher\VoucherSuggesterServiceProvider;
use Sheba\Analysis\PartnerPerformance\Providers\ServiceProvider as PartnerPerformanceServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        $this->app->register(VoucherCodeServiceProvider::class);
        $this->app->register(VoucherSuggesterServiceProvider::class);
        $this->app->register(CustomMigrationServiceProvider::class);
        $this->app->register(PartnerPerformanceServiceProvider::class);
    }
}
