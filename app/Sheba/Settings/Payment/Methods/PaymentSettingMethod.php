<?php namespace Sheba\Settings\Payment\Methods;


use App\Models\Profile;
use Sheba\Settings\Payment\Responses\InitResponse;

abstract class PaymentSettingMethod
{
    abstract public function init(Profile $profile): InitResponse;

    abstract public function validate();
}