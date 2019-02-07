<?php namespace Sheba\SmsCampaign;

use App\Models\SmsCampaignOrderReceiver;

class SmsLogs
{
    public function processLogs()
    {
        $logs = SmsCampaignOrderReceiver::where('status', constants('SMS_CAMPAIGN_RECEIVER_STATUSES.pending'))->get();
        foreach ($logs as $log) {
            dispatch((new ProcessSmsCampaignStatuses($log)));
        }
    }
}