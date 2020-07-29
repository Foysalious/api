<?php namespace Sheba\Payment\Methods\Nagad;


use App\Models\Payable;
use App\Models\Payment;
use Exception;
use Sheba\Payment\Methods\PaymentMethod;

class Nagad extends PaymentMethod
{
    const NAME='nagad';
    /**
     * @param Payable $payable
     * @return Payment
     * @throws Exception
     */
    public function init(Payable $payable): Payment
    {
        $payment= $this->createPayment($payable);

    }

    public function validate(Payment $payment): Payment
    {
        // TODO: Implement validate() method.
    }

    public function getMethodName()
    {
        return self::NAME;
    }
}
