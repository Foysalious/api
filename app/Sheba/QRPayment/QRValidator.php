<?php

namespace App\Sheba\QRPayment;

use Sheba\Dal\QRGateway\Model as QRGateway;
use Sheba\Dal\QRPayable\Contract as QRPayableRepo;
use Sheba\Dal\QRPayment\Model as QRPaymentModel;
use Sheba\Payment\Exceptions\AlreadyCompletingPayment;
use Sheba\Payment\PaymentManager;
use Sheba\Payment\Statuses;
use Sheba\QRPayment\Exceptions\QRException;
use Sheba\QRPayment\Exceptions\QRPayableNotFoundException;
use Sheba\QRPayment\Exceptions\QRPaymentAlreadyCompleted;
use Throwable;

class QRValidator
{
    private $qr_id;
    private $amount;
    private $merchant_id;
    private $payable;
    private $qr_payment;

    private $qr_payable_repo;
    private $response;
    private $gateway;

    public function __construct(QRPayableRepo $qr_payable_repo)
    {
        $this->qr_payable_repo = $qr_payable_repo;
    }

    /**
     * @param mixed $qr_id
     * @return QRValidator
     * @throws QRException
     */
    public function setQrId($qr_id): QRValidator
    {
        $this->qr_id = $qr_id;
        $this->setPayable();
        return $this;
    }

    /**
     * @param mixed $payment_method
     * @return QRValidator
     */
    public function setGateway($payment_method): QRValidator
    {
        $this->gateway = QRGateway::where('method_name', $payment_method)->first();
        return $this;
    }

    /**
     * @param mixed $response
     * @return QRValidator
     */
    public function setResponse($response): QRValidator
    {
        $this->response = json_encode($response);
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
        (new QRPaymentManager())->setQrPayment($this->qr_payment)->complete();
    }

    /**
     * @return void
     * @throws QRException
     */
    public function setPayable()
    {
        $qr_payable = $this->qr_payable_repo->where('qr_id', $this->qr_id)->first();
        if(!isset($qr_payable)) throw new QRPayableNotFoundException();

        $this->payable    = $qr_payable->payable;
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
     */
    private function makePaymentData(): array
    {
        return [
            "payable_id" => $this->payable->id,
            "qr_gateway_id" => $this->gateway->id,
            "gateway_response" => $this->response,
            "status" => "validated"
        ];
    }

    /**
     * @param mixed $amount
     * @return QRValidator
     */
    public function setAmount($amount): QRValidator
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param mixed $merchant_id
     * @return QRValidator
     */
    public function setMerchantId($merchant_id): QRValidator
    {
        $this->merchant_id = $merchant_id;
        return $this;
    }
}