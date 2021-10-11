<?php

namespace App\Sheba\Pos\Repositories;

use App\Sheba\Pos\PosClient;

class PosClientRepository extends PosClient
{
    public function addOnlinePayment()
    {

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

    public function makePaymentLinkCreateApi($partner_id, $pos_order_id): string
    {
        return "pos/v1/partners/" . $partner_id . "/orders/" . $pos_order_id . "/payment-link-created";
    }
}
