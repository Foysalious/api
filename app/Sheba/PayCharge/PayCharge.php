<?php


namespace Sheba\PayCharge;


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

    public function payCharge(PayChargable $payChargable)
    {
        return $this->method->init($payChargable);
    }

    public function complete($payment)
    {
        $response = $this->method->validate($payment);
        if ($response) {
            $pay_chargable = unserialize($payment->pay_chargable);
            $class_name = "Sheba\\PayCharge\\Complete\\" . $pay_chargable->completionClass;
            $complete_class = new $class_name();
            if ($complete_class->complete($pay_chargable, $this->method->formatTransactionData($response))) {
                return true;
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