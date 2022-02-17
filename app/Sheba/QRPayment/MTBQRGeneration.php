<?php

namespace App\Sheba\QRPayment;

use Sheba\EmvQR\EmvQR;

class MTBQRGeneration extends QRGeneration
{

    protected $method_name = "mtb";

    public function qrCodeString()
    {
        return (new EmvQR())->setAmount($this->payable->amount)->setQrName($this->method_name)->generateQrString();
    }

}