<?php namespace Sheba\Payment;

use App\Models\Payable;
use App\Models\Payment;
use ReflectionException;
use Sheba\Payment\Factory\PaymentProcessor;
use Sheba\Payment\Methods\PaymentMethod;

class ShebaPayment
{
    /** @var PaymentMethod */
    private $method;


    /**
     * @param $enum
     * @return $this
     * @throws ReflectionException
     */
    public function setMethod($enum)
    {
        $this->method = (new PaymentProcessor($enum))->method();
        return $this;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * @param Payable $payable
     * @return mixed
     */
    public function init(Payable $payable)
    {
        return $this->method->init($payable);
    }

    /**
     * @param Payment $payment
     * @return Payment
     */
    public function complete(Payment $payment)
    {
        /** @var Payment $payment */
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
