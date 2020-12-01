<?php namespace Sheba\SmsCampaign;

use Sheba\Dal\SmsCampaignOrderReceiver\SmsCampaignOrderReceiver;
use Sheba\SmsCampaign\Jobs\ProcessSmsCampaignStatusJob;

class SmsLogs
{
    public function processLogs()
    {
        foreach (SmsCampaignOrderReceiver::pending()->get() as $log) {
            dispatch(new ProcessSmsCampaignStatusJob($log));
        }
    }
}