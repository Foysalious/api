<?php namespace Sheba\Settings\Payment\Methods;


use App\Models\Profile;

abstract class PaymentSettingMethod
{
    abstract public function init(Profile $profile);

    abstract public function validate();
}