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
        $class_name = "Sheba\\PayCharge\\Complete\\" . $payment->payable->completionClass;
        $complete_class = new $class_name();
        $complete_class->complete($payment->payable, $response);
    }
}