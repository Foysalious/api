<?php namespace Sheba\Settings\Payment;


use App\Models\Profile;
use Sheba\Settings\Payment\Factory\PaymentSettingProcessor;
use Sheba\Settings\Payment\Methods\PaymentSettingMethod;

class PaymentSetting
{
    /** @var $method PaymentSettingMethod */
    private $method;

    /**
     * @param $enum
     * @return $this
     * @throws \ReflectionException
     */
    public function setMethod($enum)
    {
        $this->method = (new PaymentSettingProcessor())->setMethodName($enum)->get();
        return $this;
    }

    public function init(Profile $profile)
    {
        return $this->method->init($profile);
    }

}