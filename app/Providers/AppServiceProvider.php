<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Sheba\Dal\Providers\CustomMigrationServiceProvider;
use Sheba\Sms\SmsServiceProvider;
use Sheba\Voucher\VoucherCodeServiceProvider;
use Sheba\Voucher\VoucherSuggesterServiceProvider;
use Sheba\Analysis\PartnerPerformance\Providers\ServiceProvider as PartnerPerformanceServiceProvider;
use Sheba\Analysis\PartnerSale\Providers\ServiceProvider as PartnerSaleServiceProvider;

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
        $this->app->register(partnerPerformanceServiceProvider::class);
        $this->app->register(PartnerSaleServiceProvider::class);
        $this->app->register(SmsServiceProvider::class);
    }
}
