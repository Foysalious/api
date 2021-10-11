<?php

namespace App\Sheba\Pos\Repositories;

use App\Sheba\Pos\PosClient;

class PosClientRepository extends PosClient
{
    private $partnerId;
    private $orderId;

    public function addOnlinePayment(array $data)
    {

    }

    /**
     * @param mixed $partnerId
     * @return PosClientRepository
     */
    public function setPartnerId($partnerId): PosClientRepository
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @param mixed $orderId
     * @return PosClientRepository
     */
    public function setOrderId($orderId): PosClientRepository
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @param $data
     * @return array
     */
    public function paymentLinkCreateData($data): array
    {
        return [
            "link_id" => $data["link_id"],
            "reason" => $data["reason"],
            "link" => $data["link"],
            "emi_month" => $data["emi_month"],
            "interest" => $data["interest"],
            "bank_transaction_charge" => $data["bank_transaction_charge"],
            "paid_by" => $data["paid_by"],
            "partner_profit" => $data["partner_profit"],
            "status" => $data["status"],
        ];
    }

    public function makePaymentLinkCreateApi(): string
    {
        return "pos/v1/partners/" . $this->partnerId . "/orders/" . $this->orderId . "/payment-link-created";
    }
}
