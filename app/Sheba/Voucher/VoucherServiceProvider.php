<?php namespace Sheba\Voucher;

use Illuminate\Support\ServiceProvider;

class VoucherServiceProvider extends ServiceProvider
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
        $this->app->singleton('voucher', function ($app, $code) {
            return (new VoucherCode($code[0]))->get();
        });
    }
}