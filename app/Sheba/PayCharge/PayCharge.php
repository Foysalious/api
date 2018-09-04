<?php


namespace Sheba\PayCharge;


class PayCharge
{
    private $method;

    public function __construct($enum)
    {
        $this->method = (new PayChargeProcessor($enum))->method();
    }

    public function payCharge(PayChargable $payChargable)
    {
        return $this->method->init($payChargable);
    }

    public function complete($payment)
    {
        $response = $this->method->validate($payment);
        $pay_chargable = unserialize($payment->pay_chargable);
        $class_name = "Sheba\\PayCharge\\Complete\\" . $pay_chargable->completionClass;
        $complete_class = new $class_name();
        $complete_class->complete($pay_chargable, $response);
    }
}