<?php namespace App\Providers;

use Exception;
use Illuminate\Support\ServiceProvider;

use Sheba\Dal\Providers\CustomMigrationServiceProvider;
use Sheba\Dev\DevelopmentEnvironmentChecker;
use Sheba\Partner\HomePageSetting\Providers\ServiceProvider as PartnerHomeSettingServiceProvider;
use Sheba\Partner\HomePageSettingV3\Providers\ServiceProvider as PartnerHomeSettingServiceProviderV3;
use Sheba\PartnerOrder\ConcurrentUpdateRestriction\CURServiceProvider;
use Sheba\Recommendations\HighlyDemands\Categories\ServiceProvider as HighlyDemandsCategoriesServiceProvider;
use Sheba\Sms\SmsServiceProvider;
use Sheba\Voucher\VoucherCodeServiceProvider;
use Sheba\Voucher\VoucherSuggesterServiceProvider;
use Sheba\Analysis\PartnerPerformance\Providers\ServiceProvider as PartnerPerformanceServiceProvider;
use Sheba\Analysis\PartnerSale\Providers\ServiceProvider as PartnerSaleServiceProvider;
use Sheba\AppSettings\HomePageSetting\Getters\Provider as HomePageSettingGettersProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws Exception
     */
    public function boot()
    {
        if (!in_array($this->app->environment(), ["production", "development"])) {
            $this->app->make(DevelopmentEnvironmentChecker::class)->check();
        }
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
        $this->app->register(HomePageSettingGettersProvider::class);
        $this->app->register(PartnerHomeSettingServiceProvider::class);
        $this->app->register(PartnerHomeSettingServiceProviderV3::class);
        $this->app->register(HighlyDemandsCategoriesServiceProvider::class);
        $this->app->register(CURServiceProvider::class);
    }
}
