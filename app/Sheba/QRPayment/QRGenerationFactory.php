<?php

namespace App\Sheba\QRPayment;

use App\Models\Payable;
use Sheba\QRPayment\Exceptions\InvalidQRPaymentMethodException;

class QRGenerationFactory
{
    private $payment_method;

    private $payable;

    /**
     * @param mixed $payment_method
     * @return QRGenerationFactory
     */
    public function setPaymentMethod($payment_method): QRGenerationFactory
    {
        $this->payment_method = $payment_method;
        return $this;
    }

    /**
     * @param Payable $payable
     * @return QRGenerationFactory
     */
    public function setPayable(Payable $payable): QRGenerationFactory
    {
        $this->payable = $payable;
        return $this;
    }

    /**
     * @return QRGeneration
     * @throws InvalidQRPaymentMethodException
     */
    public function getAndSetQRClass(): QRGeneration
    {
        /** @var QRGeneration $class */
        $class = $this->getQRClass();
        return $class->setPayable($this->payable);
    }

    /**
     * @return mixed
     * @throws InvalidQRPaymentMethodException
     */
    private function getQRClass()
    {
        if ($this->payment_method === "mtb")
            return app()->make(MTBQRGeneration::class);

        throw new InvalidQRPaymentMethodException();
    }
}