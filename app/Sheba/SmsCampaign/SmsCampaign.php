<?php namespace Sheba\SmsCampaign;


use App\Models\SmsCampaignOrder;
use App\Sheba\SmsCampaign\InfoBip\SmsHandler;
use Sheba\SmsCampaign\InfoBip\InfoBip;

class SmsCampaign
{
    private $smsHandler;
    public function __construct(SmsHandler $smsHandler)
    {
        $this->smsHandler = $smsHandler;
    }

    public function createOrder()
    {
//        $this->smsHandler->sendBulkMessages($to, $message);
    }

//    public function sendBulkMessage($to,$message)
//    {
//        $this->smsHandler->sendBulkMessages($to, $message)
//    }
//
//    public function processSmsLogs()
//    {
//
//    }
}