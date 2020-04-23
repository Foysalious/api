<?php namespace Sheba\Payment;

use App\Models\Payable;
use App\Models\Payment;
use App\Sheba\Payment\Policy\PaymentInitiate;
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
     * @return Payment
     * @throws Adapters\Error\InitiateFailedException
     */
    public function init(Payable $payable)
    {
        /** @var PaymentInitiate $payment_initiate */
        $payment_initiate = app(PaymentInitiate::class);
        $payment_initiate->setPaymentMethod($this->method)->setPayableType($payable->type)->setPayableTypeId($payable->type_id)->canPossible();
        return $this->method->init($payable);
    }

    /**
     * @param Payment $payment
     * @return Payment
     */
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