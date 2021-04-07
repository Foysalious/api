<?php namespace Sheba\SmsCampaign\DTO;


class VendorSmsDTO
{
    const PENDING_STATUS = 'PENDING';
    const SUCCESSFUL_STATUS = 'DELIVERED';

    private $status;

    public function __construct($sms_details = null)
    {
        $this->status = self::PENDING_STATUS;
        $this->setResponse($sms_details);
    }

    public function setResponse($sms_details)
    {
        if (empty($sms_details)) return;

        $this->status = $sms_details->status->name;
    }

    public function isPending()
    {
        return $this->checkStatus(self::PENDING_STATUS);
    }

    public function isSuccessful()
    {
        return $this->checkStatus(self::SUCCESSFUL_STATUS);
    }

    private function checkStatus($status)
    {
        return (bool)(strpos($this->status, $status) !== false);
    }
}