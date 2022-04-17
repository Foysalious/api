<?php

namespace App\Sheba\QRPayment;

use App\Sheba\QRPayment\Methods\MTB\MtbQr;
use Exception;

class MTBQRGeneration extends QRGeneration
{

    protected $method_name = "mtb";

    protected $qrId;

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
        /** @var MtbQr $mtbQr */
        $mtbQr = app(MtbQr::class);
        $mtbQr->setMerchantId($this->financial_information->mtb_merchant_id)->setAmount($this->payable->amount)->getMTBQRString();
        $this->setQrId($mtbQr->getRefId());
        return $mtbQr->getQrString();
    }

}