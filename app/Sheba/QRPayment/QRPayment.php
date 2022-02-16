<?php

namespace App\Sheba\QRPayment;

use App\Models\Partner;
use App\Models\Payable;

class QRPayment
{
    private $partner;

    private $data;

    /**
     * @param mixed $partner
     * @return QRPayment
     */
    public function setPartner($partner): QRPayment
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

    public function generate()
    {
        $this->store_payable();
        return 123;
    }

    private function store_payable()
    {
        $data = $this->make_data();
        dd($data);
        Payable::create();
    }

    private function make_data(): array
    {
        return [
            "type"            => $this->data->type,
            "type_id"         => $this->data->type_id,
            "user_type"       => "App\\Models\\Customer",
            "user_id"         => 190849,
            "amount"          => $this->data->amount,
            "completion_type" => $this->data->type,
            "payee_id"        => $this->partner->id,
            "payee_type"      => strtolower(class_basename($this->partner)),
        ];
    }
}