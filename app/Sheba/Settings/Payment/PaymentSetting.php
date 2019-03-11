<?php namespace Sheba\Settings\Payment;


use App\Models\Profile;
use Exception;
use Illuminate\Support\Facades\Redis;
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


    /**
     * @param Profile $profile
     * @return Responses\InitResponse
     * @throws Exception
     */
    public function init(Profile $profile)
    {
        if (!isset($this->method)) throw new Exception("Payment Setting Method is not set");
        return $this->method->init($profile);
    }

    public function save($payment_id)
    {
        $response = $this->method->validate($payment_id);
        $key = json_decode(Redis::get($payment_id));
        $model_name = "App\\Models\\" . $key->type;
        $model = $model_name::find((int)$key->id);
        return $this->method->save($model, $response->agreementId);
    }

}