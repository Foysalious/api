<?php

namespace App\Sheba\QRPayment\Complete;

use Sheba\Dal\QRPayment\Model as QRPaymentModel;

abstract class QRPaymentComplete
{
    /*** @var QRPaymentModel */
    protected $qr_payment;

    protected $payable;

    protected $method;

    /**
     * @param mixed $qr_payment
     * @return QRPaymentComplete
     */
    public function setQrPayment(QRPaymentModel $qr_payment): QRPaymentComplete
    {
        $this->qr_payment = $qr_payment;
        return $this;
    }

    /**
     * @param mixed $payable
     * @return QRPaymentComplete
     */
    public function setPayable($payable): QRPaymentComplete
    {
        $this->payable = $payable;
        return $this;
    }

    /**
     * @param mixed $method
     * @return QRPaymentComplete
     */
    public function setMethod($method): QRPaymentComplete
    {
        $this->method = $method;
        return $this;
    }

    public abstract function complete();

}