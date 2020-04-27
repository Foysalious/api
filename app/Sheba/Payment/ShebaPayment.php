<?php namespace Sheba\Payment;

use App\Models\Payable;
use App\Models\Payment;
use Sheba\Payment\Policy\PaymentInitiate;
use ReflectionException;
use Sheba\Payment\Exceptions\InitiateFailedException;
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
     * @return bool true if can init
     * @throws InitiateFailedException otherwise
     */
    private function canInit(Payable $payable)
    {
        /** @var PaymentInitiate $payment_initiate */
        $payment_initiate = app(PaymentInitiate::class);
        return $payment_initiate->setPaymentMethod($this->method)->setPayable($payable)->canPossible();
    }


    /**
     * @param Payable $payable
     * @return Payment
     * @throws InitiateFailedException
     */
    public function init(Payable $payable)
    {
        $this->canInit($payable);
        $payment = $this->method->init($payable);
        if (!$payment->isInitiated()) throw new InitiateFailedException('Payment initiation failed!');
        return $payment;
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
