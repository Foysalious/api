<?php namespace Sheba\Payment;

use App\Models\Payable;
use App\Models\Payment;
use Sheba\Payment\Factory\PaymentProcessor;

class ShebaPayment
{
    /**
     * ShebaPayment constructor.
     * @param $enum
     * @throws \ReflectionException
     */
    public function __construct($enum)
    {
        $this->method = (new PaymentProcessor($enum))->method();
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    public function init(Payable $payable)
    {
        return $this->method->init($payable);
    }

    public function complete(Payment $payment)
    {
        $payment = $this->method->validate($payment);
        if ($payment->canComplete()) {
            /** @var Payable $payable */
            $payable = $payment->payable;
            $completion_class = $payable->getCompletionClass();
            $completion_class->setPayment($payment);
            $payment = $completion_class->complete();
        }
        return $payment;
    }
}