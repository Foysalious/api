<?php namespace Sheba\Payment\Methods\Nagad;


use App\Models\Payable;
use App\Models\Payment;
use Exception;
use Sheba\Payment\Methods\PaymentMethod;

class Nagad extends PaymentMethod
{

    /**
     * @param Payable $payable
     * @return Payment
     * @throws Exception
     */
    public function init(Payable $payable): Payment
    {
        return $this->createPayment($payable);
    }

    public function validate(Payment $payment): Payment
    {
        // TODO: Implement validate() method.
    }

    public function getMethodName()
    {
        // TODO: Implement getMethodName() method.
    }
}
