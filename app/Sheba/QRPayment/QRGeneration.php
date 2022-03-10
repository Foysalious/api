<?php

namespace App\Sheba\QRPayment;

use App\Models\Payable;
use Sheba\Dal\PartnerFinancialInformation\Model as PartnerFinancialInformation;

abstract class QRGeneration
{
    protected $method_name;

    protected $payable;

    protected $qr_id;

    protected $financial_information;

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


    /**
     * @param mixed $financial_information
     * @return QRGeneration
     */
    public function setFinancialInformation(PartnerFinancialInformation $financial_information): QRGeneration
    {
        $this->financial_information = $financial_information;
        return $this;
    }

    public abstract function qrCodeString();

    public abstract function generateQRId();

}