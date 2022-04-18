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
            "status" => "Pending",
            "description" => MtbAccountStatusConstants::REQUEST_PLACED
        ];
        if ($this->status == "15") return [
            "status" => "Processing",
            "description" => MtbAccountStatusConstants::CIF_CREATED
        ];
        if ($this->status == "17") return [
            "status" => "Processing",
            "description" => MtbAccountStatusConstants::ACCOUNT_OPENED_CBS
        ];
        if ($this->status == "19") return [
            "status" => "Completed",
            "description" => MtbAccountStatusConstants::MERCHANT_CREATED
        ];
        if ($this->status == "20") return [
            "status" => "Cancelled",
            "description" => MtbAccountStatusConstants::REQUEST_CANCELLED
        ];

    }


}
