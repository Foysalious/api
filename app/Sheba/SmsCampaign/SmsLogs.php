<?php namespace Sheba\SmsCampaign;

use Sheba\SmsCampaign\InfoBip\InfoBip;

class SmsLogs
{
    public function getSingleMessage(InfoBip $infoBip, $message_id)
    {
        return $infoBip->get('/sms/2/logs',['messageId'=> $message_id])[0];
    }

    public function bulk(InfoBip $infoBip, $bulk_id)
    {
        return $infoBip->get('/sms/2/logs',['bulkId'=> $bulk_id]);
    }
}