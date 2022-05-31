<?php

namespace App\Sheba\QRPayment;

use App\Models\Payable;
use Sheba\QRPayment\Exceptions\InvalidQRPaymentMethodException;

class QRGenerationFactory
{
    private $payment_method;

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
     * @return QRGeneration
     * @throws InvalidQRPaymentMethodException
     */
    public function get(): QRGeneration
    {
        /** @var QRGeneration $class */
        return $this->getQRClass();
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