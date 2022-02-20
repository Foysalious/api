<?php

namespace App\Sheba\QRPayment;

use Sheba\EmvQR\EmvQR;

class MTBQRGeneration extends QRGeneration
{

    protected $method_name = "mtb";

    protected $qr_id;

    public function generateQrId(): string
    {
        return sprintf('%08X', mt_rand(0, 4294967295));
    }

    public function qrCodeString(): string
    {
        return (new EmvQR())->setQrId($this->qr_id)->setAmount($this->payable->amount)
            ->setQrName($this->method_name)->generateQrString();
    }

}