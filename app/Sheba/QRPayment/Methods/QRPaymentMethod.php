<?php

namespace App\Sheba\QRPayment\Methods;

use App\Sheba\QRPayment\Methods\MTB\MtbQr;

abstract class QRPaymentMethod
{
    protected $amount;

    protected $merchantId;

    public abstract function validate();

    /**
     * @param mixed $amount
     * @return QRPaymentMethod
     */
    public function setAmount($amount): QRPaymentMethod
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $merchantId
     * @return MtbQr
     */
    public function setMerchantId($merchantId): MtbQr
    {
        $this->merchantId = $merchantId;
        return $this;
    }
}