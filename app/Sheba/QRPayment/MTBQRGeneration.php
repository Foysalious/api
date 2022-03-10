<?php

namespace App\Sheba\QRPayment;

use Exception;
use Sheba\EmvQR\EmvQR;

class MTBQRGeneration extends QRGeneration
{

    protected $method_name = "mtb";

    protected $qr_id;

    public function generateQrId(): string
    {
        return sprintf('%08X', mt_rand(0, 4294967295));
    }

    /**
     * @return string
     * @throws Exception
     */
    public function qrCodeString(): string
    {
        return (new EmvQR())->setQrId($this->qr_id)->setAmount($this->payable->amount)->setMerchantId($this->financial_information->mtb_merchant_id)
            ->setMasterCard($this->financial_information->master_card_number)->setUnionPayCard($this->financial_information->union_pay)
            ->setVisaCard($this->financial_information->visa_card_number)->setQrName($this->method_name)->setMerchantCategory('5814')
            ->generateQrString();
    }

}