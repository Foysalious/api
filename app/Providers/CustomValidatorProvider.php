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
        Validator::extend('is_variables_present', $this->validatorNamespace . 'SmsTemplateValidator@validate');
        Validator::extend('resolution', $this->validatorNamespace . 'ImageResolutionValidator@validate');
        Validator::extend('aspect', $this->validatorNamespace . 'ImageAspectRatioValidator@validate');
        Validator::extend('after_or_equal', $this->validatorNamespace . 'CustomDateValidator@afterOrEqual');
        Validator::extend('before_or_equal', $this->validatorNamespace . 'CustomDateValidator@beforeOrEqual');
        Validator::extend('after_or_equal_not_if', $this->validatorNamespace . 'CustomDateValidator@afterOrEqualIf');
        Validator::extend('unique_and_valid_app_version', $this->validatorNamespace . 'AppVersionValidator@uniqueAndValidVersion');
        Validator::extend('date_if_not', $this->validatorNamespace . 'CustomDateValidator@dateIfNot');
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
