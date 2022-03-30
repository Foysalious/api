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
        $response = $this->method->init($profile);
        $this->setPaymentIdInRedis($response->getTransactionId(), $profile);
        return $response;
    }

    public function setPaymentIdInRedis($transaction_id, Profile $profile)
    {
        Redis::set($transaction_id, json_encode(array(
            'id' => $profile->id,
            'type' => 'profile'
        )));
        Redis::expire($transaction_id, 120 * 60 * 60);
    }

    public function save($transaction_id)
    {
        $response = $this->method->validate($transaction_id);
        $key = json_decode(Redis::get($transaction_id));
        $profile = Profile::find((int)$key->id);
        return $this->method->save($profile, $response->getAgreementId());
    }

}