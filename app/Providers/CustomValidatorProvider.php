<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Validator;

class CustomValidatorProvider extends ServiceProvider
{
    private $validatorNamespace = 'App\Http\Validators\\';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('mobile', $this->validatorNamespace . 'MobileNumberValidator@validate');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
