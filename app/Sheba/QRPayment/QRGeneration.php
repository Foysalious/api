<?php

namespace App\Sheba\QRPayment;

use App\Models\Payable;

abstract class QRGeneration
{
    protected $method_name;

    protected $payable;

    /**
     * @param mixed $payable
     * @return QRGeneration
     */
    public function setPayable(Payable $payable): QRGeneration
    {
        $this->payable = $payable;
        return $this;
    }

    public abstract function qrCodeString();

}