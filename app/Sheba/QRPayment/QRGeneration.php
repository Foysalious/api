<?php

namespace App\Sheba\QRPayment;

use App\Models\Payable;

abstract class QRGeneration
{
    protected $method_name;

    protected $payable;

    protected $qr_id;

    /**
     * @param mixed $payable
     * @return QRGeneration
     */
    public function setPayable(Payable $payable): QRGeneration
    {
        $this->payable = $payable;
        return $this;
    }

    /**
     * @param $qr_id
     * @return $this
     */
    public function setQrId($qr_id): QRGeneration
    {
        $this->qr_id = $qr_id;
        return $this;
    }

    public abstract function qrCodeString();

    public abstract function generateQRId();

}