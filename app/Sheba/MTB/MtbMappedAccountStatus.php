<?php

namespace App\Sheba\MTB;

class MtbMappedAccountStatus
{
    private $status;

    public function setStatus($status): MtbMappedAccountStatus
    {
        $this->status = $status;
        return $this;
    }

    public function mapMtbAccountStatus()
    {
        if ($this->status == "11") return [
            "status" => "pending",
            "description" => MtbAccountStatusConstants::REQUEST_PLACED
        ];
        if ($this->status == "15") return [
            "status" => "processing",
            "description" => MtbAccountStatusConstants::CIF_CREATED
        ];
        if ($this->status == "17") return [
            "status" => "processing",
            "description" => MtbAccountStatusConstants::ACCOUNT_OPENED_CBS
        ];
        if ($this->status == "19") return [
            "status" => "completed",
            "description" => MtbAccountStatusConstants::MERCHANT_CREATED
        ];
        if ($this->status == "20") return [
            "status" => "cancelled",
            "description" => MtbAccountStatusConstants::REQUEST_CANCELLED
        ];

    }


}
