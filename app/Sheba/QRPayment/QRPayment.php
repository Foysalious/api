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
use Sheba\QRPayment\Exceptions\InvalidQRPaymentMethodException;

class QRPayment
{
    private $partner;

    /*** @var QRGeneratePayload */
    private $data;

    private $payable;

    private $qr_string;
    private $qr_id;

    /**
     * @param mixed $partner
     * @return QRPayment
     */
    public function setPartner(Partner $partner): QRPayment
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $data
     * @return QRPayment
     */
    public function setData(QRGeneratePayload $data): QRPayment
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param mixed $qr_id
     * @return QRPayment
     */
    public function setQrId($qr_id): QRPayment
    {
        $this->qr_id = $qr_id;
        return $this;
    }

    /**
     * @param mixed $qr_string
     * @return QRPayment
     */
    public function setQrString($qr_string): QRPayment
    {
        $this->qr_string = $qr_string;
        return $this;
    }

    /**
     * @param mixed $payable
     */
    public function setPayable($payable)
    {
        $this->payable = $payable;
    }

    /**
     * @throws CustomerNotFoundException
     * @throws InvalidQRPaymentMethodException
     * @throws PosOrderServiceServerError
     */
    public function generate(): QRPayment
    {
        $this->storePayable();
        $this->generateQR();
        $this->storeQRPayable();
        return $this;
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
     * @throws InvalidQRPaymentMethodException
     */
    private function generateQR()
    {
        $qr_code_generate = (new QRGenerationFactory())->setPaymentMethod($this->data->payment_method)->get();
        $qr_id            = $qr_code_generate->generateQrId();
        $this->setQrId($qr_id);
        $qr_string        = $qr_code_generate->setQrId($this->qr_id)->setPayable($this->payable)->qrCodeString();
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
            "qr_string"  => $this->qr_string,
            "qr_id"      => $this->qr_id,
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
            $type_id   = $pos_order->id;
        }

        /** @var PosCustomerResolver $posCustomerResolver */
        $posCustomerResolver = app(PosCustomerResolver::class);
        if($this->data->payer_type === "pos_customer")
            $customer = $posCustomerResolver->setCustomerId($this->data->payer_id)->setPartner($this->partner)->get();

        if(!isset($customer))
            throw new CustomerNotFoundException();

        if (($this->data->type === "accounting_due"))
            $type_id = (int)$customer->id;


        return [
            "type"            => $this->data->type,
            "type_id"         => $type_id,
            "user_type"       => "pos_customer",
            "user_id"         => $customer->id,
            "amount"          => $this->data->amount,
            "completion_type" => $this->data->type,
            "payee_id"        => $this->partner->id,
            "payee_type"      => strtolower(class_basename($this->partner)),
        ];
    }

    /**
     * @return mixed
     */
    public function getQrString()
    {
        return $this->qr_string;
    }

    /**
     * @return mixed
     */
    public function getQrId()
    {
        return $this->qr_id;
    }
}