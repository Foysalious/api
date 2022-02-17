<?php

namespace App\Sheba\QRPayment;

use App\Models\Partner;
use App\Models\Payable;
use App\Sheba\PosOrderService\Exceptions\PosOrderServiceServerError;
use Sheba\Dal\QRPayable\Model as QRPayable;
use Sheba\Pos\Customer\PosCustomerResolver;
use Sheba\Pos\Order\PosOrderResolver;
use Sheba\QRPayment\Exceptions\CustomerNotFoundException;
use Sheba\QRPayment\Exceptions\InvalidQRPaymentMethodException;

class QRPayment
{
    private $partner;

    private $data;

    private $payable;

    private $qr_string;

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
    public function setData($data): QRPayment
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     * @throws CustomerNotFoundException
     * @throws InvalidQRPaymentMethodException
     * @throws PosOrderServiceServerError
     */
    public function generate()
    {
        $this->store_payable();
        $this->generate_qr();
        $this->store_qr_payable();
        return $this->qr_string;
    }

    /**
     * @return void
     * @throws CustomerNotFoundException
     * @throws PosOrderServiceServerError
     */
    private function store_payable()
    {
        $data = $this->make_data();
        $this->payable = Payable::create($data);
    }

    /**
     * @return void
     * @throws InvalidQRPaymentMethodException
     */
    private function generate_qr()
    {
        $qr_code_generate = (new QRGenerationFactory())->setPayable($this->payable)->setPaymentMethod($this->data->payment_method)->getAndSetQRClass();
        $this->qr_string  = $qr_code_generate->qrCodeString();
    }

    private function store_qr_payable()
    {
        $data = $this->make_qr_payment_data();
        QRPayable::create($data);
    }

    private function make_qr_payment_data(): array
    {
        return [
            "payable_id" => $this->payable->id,
            "qr_string"  => $this->qr_string,
            "qr_id"      => time(),
        ];
    }

    /**
     * @return array
     * @throws CustomerNotFoundException
     * @throws PosOrderServiceServerError
     */
    private function make_data(): array
    {
        if (($this->data->payable_type === "pos_order")) {
            /** @var PosOrderResolver $posOrderResolver */
            $posOrderResolver = app(PosOrderResolver::class);
            $pos_order = $posOrderResolver->setOrderId($this->data->target_id)->get();
            $type_id   = $pos_order->id;
        }

        /** @var PosCustomerResolver $posCustomerResolver */
        $posCustomerResolver = app(PosCustomerResolver::class);
        $customer = $posCustomerResolver->setCustomerId($this->data->customer_id)->setPartner($this->partner)->get();

        if(!isset($customer))
            throw new CustomerNotFoundException();

        if (($this->data->payable_type === "accounting_due"))
            $type_id = (int)$customer->id;


        return [
            "type"            => $this->data->payable_type,
            "type_id"         => $type_id,
            "user_type"       => "pos_customer",
            "user_id"         => $customer->id,
            "amount"          => $this->data->amount,
            "completion_type" => $this->data->payable_type,
            "payee_id"        => $this->partner->id,
            "payee_type"      => strtolower(class_basename($this->partner)),
        ];
    }
}