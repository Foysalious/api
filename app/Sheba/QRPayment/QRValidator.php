<?php

namespace App\Sheba\QRPayment;

use Sheba\Dal\QRPayable\Contract as QRPayableRepo;
use Sheba\Dal\QRPayment\Model as QRPaymentModel;
use Sheba\Payment\Exceptions\AlreadyCompletingPayment;
use Sheba\Payment\Statuses;
use Sheba\QRPayment\Exceptions\QRException;
use Sheba\QRPayment\Exceptions\QRPayableNotFoundException;
use Sheba\QRPayment\Exceptions\QRPaymentAlreadyCompleted;
use Throwable;

class QRValidator
{
    private $qr_id;

    private $qr_payable;
    private $payable;
    private $qr_payment;

    private $qr_payable_repo;
    private $payment_method;
    private $response;

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

    /**
     * @param mixed $payment_method
     * @return QRValidator
     */
    public function setPaymentMethod($payment_method): QRValidator
    {
        $this->payment_method = $payment_method;
        return $this;
    }

    /**
     * @param mixed $response
     * @return QRValidator
     */
    public function setResponse($response): QRValidator
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return void
     * @throws AlreadyCompletingPayment
     * @throws QRException
     * @throws Throwable
     */
    public function complete()
    {
        $this->storePayment();
        $this->qrPaymentComplete();
    }

    /**
     * @return void
     * @throws AlreadyCompletingPayment
     * @throws Throwable
     */
    private function qrPaymentComplete()
    {
        (new QRPaymentManager())->setQrPayment($this->qr_payment)->setMethod($this->payment_method)
            ->setPayable($this->payable)->complete();
    }

    /**
     * @return void
     * @throws QRException
     */
    public function setPayables()
    {
        $this->qr_payable = $this->qr_payable_repo->where('qr_id', $this->qr_id)->first();
        if(!isset($this->qr_payable)) throw new QRPayableNotFoundException();

        $this->payable    = $this->qr_payable->payable;
    }

    /**
     * @return void
     * @throws QRException
     */
    private function storePayment()
    {
        $data = $this->makePaymentData();
        $this->checkIsCompleted();
        $this->qr_payment = QRPaymentModel::create($data);
    }

    /**
     * @return void
     * @throws QRException
     */
    private function checkIsCompleted()
    {
        $qr_payment = QRPaymentModel::query()->where("payable_id", $this->payable->id)
            ->where("status", Statuses::COMPLETED)->first();
        if(isset($qr_payment))
            throw new QRPaymentAlreadyCompleted();
    }

    /**
     * @return array
     * @throws QRException
     */
    private function makePaymentData(): array
    {
        $this->setPayables();
        return [
            "payable_id" => $this->payable->id,
            "gateway_account_name" => $this->payment_method,
            "gateway_response" => $this->response,
            "status" => "validated"
        ];
    }
}