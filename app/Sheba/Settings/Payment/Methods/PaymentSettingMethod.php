<?php namespace Sheba\Settings\Payment\Methods;


use App\Models\Profile;
use Sheba\Settings\Payment\Responses\InitResponse;
use Sheba\Settings\Payment\Responses\ValidateResponse;

abstract class PaymentSettingMethod
{
    abstract public function init(Profile $profile): InitResponse;

    abstract public function validate($id): ValidateResponse;

    abstract public function save(Profile $profile, $id): Profile;

}