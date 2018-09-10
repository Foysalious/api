<?php


namespace Sheba\PayCharge;

use Cache;

class PayCharge
{
    private $method;
    private $message;

    public function __construct($enum)
    {
        $this->method = (new PayChargeProcessor($enum))->method();
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function init(PayChargable $payChargable)
    {
        return $this->method->init($payChargable);
    }

    public function complete($redis_key)
    {
        $payment = Cache::store('redis')->get("paycharge::$redis_key");
        $payment = json_decode($payment);
        if ($response = $this->method->validate($payment)) {
            $pay_chargable = unserialize($payment->pay_chargable);
            $class_name = "Sheba\\PayCharge\\Complete\\" . $pay_chargable->completionClass;
            $complete_class = new $class_name();
            if ($complete_class->complete($pay_chargable, $this->method->formatTransactionData($response))) {
                Cache::store('redis')->forget("paycharge::$redis_key");
                return array('redirect_url' => $pay_chargable->redirectUrl);
            } else {
                $this->message = "Paycharge completion failed";
                return false;
            }
        } else {
            $this->message = $this->method->message;
            return false;
        }
    }
}