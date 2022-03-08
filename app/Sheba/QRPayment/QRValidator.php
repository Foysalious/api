<?php

namespace App\Sheba\QRPayment;

use App\Models\Partner;
use App\Models\Payable;
use App\Sheba\QRPayment\DTO\QRGeneratePayload;
use Sheba\Dal\PartnerFinancialInformation\Model as PartnerFinancialInformation;
use Sheba\Dal\QRGateway\Model as QRGateway;
use Sheba\Dal\QRPayable\Contract as QRPayableRepo;
use Sheba\Dal\QRPayment\Model as QRPaymentModel;
use Sheba\Payment\Exceptions\AlreadyCompletingPayment;
use Sheba\Payment\Statuses;
use Sheba\QRPayment\Exceptions\FinancialInformationNotFoundException;
use Sheba\QRPayment\Exceptions\QRException;
use Sheba\QRPayment\Exceptions\QRPayableNotFoundException;
use Sheba\QRPayment\Exceptions\QRPaymentAlreadyCompleted;
use Throwable;

class QRValidator
{
    private $qrId;
    private $amount;
    private $merchantId;
    /** @var Payable */
    private $payable;
    /*** @var QRPaymentModel */
    private $qrPayment;
    /*** @var QRPayableRepo */
    private $qrPayableRepo;
    private $request;
    private $gateway;

    public function __construct(QRPayableRepo $qr_payable_repo)
    {
        $this->qrPayableRepo = $qr_payable_repo;
    }

    /**
     * @param mixed $qrId
     * @return QRValidator
     * @throws QRException
     */
    public function setQrId($qrId): QRValidator
    {
        $this->qrId = $qrId;
        if ($this->qrId) {
            $qr_payable = $this->qrPayableRepo->where('qr_id', $this->qrId)->first();
            if (!isset($qr_payable)) throw new QRPayableNotFoundException();
            $this->setPayable($qr_payable->payable);
        }
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
     * @param mixed $request
     * @return QRValidator
     */
    public function setRequest($request): QRValidator
    {
        $this->request = json_encode($request);
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
        if (!isset($this->qrId)) {
            $partner = $this->getPartnerFromMerchantId();
            $data = new QRGeneratePayload([
                "amount" => $this->amount,
                "payment_method" => $this->gateway->method_name
            ]);
            $qr_payable = (new QRPayableGenerator())->setPartner($partner)->setData($data)->getQrPayable();
            $this->setPayable($qr_payable->payable);
        }
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
        (new QRPaymentManager())->setQrPayment($this->qrPayment)->complete();
    }

    /**
     * @return void
     */
    public function setPayable(Payable $payable)
    {
        $this->payable = $payable;
    }

    /**
     * @return void
     * @throws QRException
     */
    private function storePayment()
    {
        $data = $this->makePaymentData();
        $this->checkIsCompleted();
        $this->qrPayment = QRPaymentModel::create($data);
    }

    /**
     * @return void
     * @throws QRException
     */
    private function checkIsCompleted()
    {
        $qr_payment = QRPaymentModel::query()->where("payable_id", $this->payable->id)
            ->where("status", Statuses::COMPLETED)->first();
        if (isset($qr_payment))
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
            "gateway_response" => $this->request,
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
     * @param mixed $merchantId
     * @return QRValidator
     */
    public function setMerchantId($merchantId): QRValidator
    {
        $this->merchantId = $merchantId;
        return $this;
    }

    /**
     * @throws QRException
     */
    private function getPartnerFromMerchantId(): Partner
    {
        return Partner::find(38015);
        $finance_information = PartnerFinancialInformation::query()->where("mtb_merchant_id", $this->merchantId)->first();
        if (!$finance_information) throw new FinancialInformationNotFoundException();
        return $finance_information->partner;
    }
}