<?php

namespace App\Sheba\QRPayment;

use Sheba\Dal\QRPayable\Contract as QRPayableRepo;

class QRValidator
{
    private $qr_id;

    private $qr_payable;
    private $payable;

    private $qr_payable_repo;

    public function __construct(QRPayableRepo $qr_payable_repo)
    {
        $this->qr_payable_repo = $qr_payable_repo;
    }

    /**
     * @param mixed $qr_id
     * @return QRValidator
     */
    public function setQrId($qr_id): QRValidator
    {
        $this->qr_id = $qr_id;
        return $this;
    }

    public function validate()
    {
        $this->setPayables();
        dd($this->payable);
    }

    public function setPayables()
    {
        $this->qr_payable = $this->qr_payable_repo->where('qr_id', $this->qr_id)->first();
        $this->payable    = $this->qr_payable->payable;
    }
}