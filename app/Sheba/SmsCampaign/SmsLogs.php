<?php namespace Sheba\SmsCampaign;

use App\Models\SmsCampaignOrderReceiver;
use Sheba\SmsCampaign\Jobs\ProcessSmsCampaignStatusJob;

class SmsLogs
{
    public function processLogs()
    {
        $logs = SmsCampaignOrderReceiver::where('status', constants('SMS_CAMPAIGN_RECEIVER_STATUSES.pending'))->get();
        foreach ($logs as $log) {
            dispatch(new ProcessSmsCampaignStatusJob($log));
        }
    }
}