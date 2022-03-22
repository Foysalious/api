<?php

namespace App\Sheba\QRPayment;

use App\Models\Partner;
use App\Models\Payable;
use App\Sheba\PosOrderService\Exceptions\PosOrderServiceServerError;
use App\Sheba\QRPayment\DTO\QRGeneratePayload;
use Exception;
use Sheba\Dal\QRPayable\Model as QRPayable;
use Sheba\Pos\Customer\PosCustomerResolver;
use Sheba\Pos\Order\PosOrderResolver;
use Sheba\QRPayment\Exceptions\CustomerNotFoundException;
use Sheba\QRPayment\Exceptions\FinancialInformationNotFoundException;
use Sheba\QRPayment\Exceptions\QRException;

class QRPayableGenerator implements QrPayableAdapter
{
    /** @var Partner */
    private $partner;
    /*** @var QRGeneratePayload */
    private $data;
    /** @var Payable */
    private $payable;

    /**
     * @param mixed $partner
     * @return QRPayableGenerator
     */
    public function setPartner(Partner $partner): QRPayableGenerator
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $data
     * @return QRPayableGenerator
     */
    public function setData(QRGeneratePayload $data): QRPayableGenerator
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param mixed $qr_id
     * @return QRPayableGenerator
     */
    public function setQrId($qr_id): QRPayableGenerator
    {
        $this->qr_id = $qr_id;
        return $this;
    }

    /**
     * @param mixed $qr_string
     * @return QRPayableGenerator
     */
    public function setQrString($qr_string): QRPayableGenerator
    {
        $this->qr_string = $qr_string;
        return $this;
    }

    /**
     * @param mixed $payable
     */
    private function setPayable($payable)
    {
        $this->payable = $payable;
    }

    /**
     * @return void
     * @throws CustomerNotFoundException
     * @throws PosOrderServiceServerError
     */
    private function storePayable()
    {
        $data = $this->makePayableData();
        $payable = Payable::create($data);
        $this->setPayable($payable);
    }

    /**
     * @return void
     * @throws QRException
     */
    private function generateQR()
    {
        $qr_code_generate = (new QRGenerationFactory())->setPaymentMethod($this->data->payment_method)->get();
        $qr_id = $qr_code_generate->generateQrId();
        $this->setQrId($qr_id);
        $partner_finance_information = $this->partner->financialInformations;
        if (!isset($partner_finance_information)) throw new FinancialInformationNotFoundException();
        $qr_string = $qr_code_generate->setQrId($this->qr_id)
            ->setFinancialInformation($partner_finance_information)->setPayable($this->payable)->qrCodeString();
        $this->setQrString($qr_string);
    }

    private function storeQRPayable()
    {
        $data = $this->makeQRPayableData();
        QRPayable::create($data);
    }

    private function makeQRPayableData(): array
    {
        return [
            "payable_id" => $this->payable->id,
            "qr_string" => $this->qr_string,
            "qr_id" => $this->qr_id,
        ];
    }

    /**
     * @return array
     * @throws CustomerNotFoundException
     * @throws PosOrderServiceServerError
     * @throws Exception
     */
    private function makePayableData(): array
    {
        if (($this->data->type === "pos_order")) {
            /** @var PosOrderResolver $posOrderResolver */
            $posOrderResolver = app(PosOrderResolver::class);
            $pos_order = $posOrderResolver->setOrderId($this->data->type_id)->get();
            $type_id = $pos_order->id;
        }

        /** @var PosCustomerResolver $posCustomerResolver */
        $posCustomerResolver = app(PosCustomerResolver::class);
        if ($this->data->payer_type === "pos_customer") {
            $customer = $posCustomerResolver->setCustomerId($this->data->payer_id)->setPartner($this->partner)->get();

            if (!isset($customer))
                throw new CustomerNotFoundException();
        }

        if (($this->data->type === "accounting_due"))
            $type_id = isset($customer) ? (int)$customer->id : null;


        return [
            "type" => $this->data->type ?? null,
            "type_id" => $type_id ?? null,
            "user_type" => $this->data->payer_type,
            "user_id" => isset($customer) ? $customer->id : null,
            "amount" => $this->data->amount,
            "completion_type" => $this->data->type ?? null,
            "payee_id" => $this->partner->id,
            "payee_type" => strtolower(class_basename($this->partner)),
        ];
    }

    /**
     * @return QRPayable
     * @throws CustomerNotFoundException
     * @throws PosOrderServiceServerError
     * @throws QRException
     * @throws Exception
     */
    public function getQrPayable(): QRPayable
    {
        $this->storePayable();
        $this->generateQR();
        $this->storeQRPayable();
        return $this->getPayable()->qrPayable;
    }

    /**
     * @throws Exception
     */
    public function getPayable(): Payable
    {
        if (!$this->payable) throw new Exception("Need to generate Qr Payable first");
        return $this->payable;
    }

    public function setModelForPayable($model)
    {
        // TODO: Implement setModelForPayable() method.
    }

    public function setEmiMonth($month)
    {
        // TODO: Implement setEmiMonth() method.
    }

    public function canInit(): bool
    {
        // TODO: Implement canInit() method.
    }

}