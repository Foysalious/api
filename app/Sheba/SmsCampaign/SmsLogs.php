<?php namespace Sheba\SmsCampaign;

use App\Models\SmsCampaignOrderReceiver;
use App\Sheba\SmsCampaign\InfoBip\SmsHandler;

class SmsLogs
{
    public function processLogs(SmsHandler $smsHandler)
    {
        $logs = SmsCampaignOrderReceiver::where('status', constants('SMS_CAMPAIGN_RECEIVER_STATUSES.pending'))->get();
        foreach ($logs as $log) {
            (new ProcessSmsCampaignStatuses($log))->handle($smsHandler);
            //dispatch((new ProcessSmsCampaignStatuses($log)));
        }
    }
}