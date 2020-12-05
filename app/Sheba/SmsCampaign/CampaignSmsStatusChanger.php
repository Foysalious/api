<?php namespace Sheba\SmsCampaign;

use Sheba\Dal\SmsCampaignOrderReceiver\SmsCampaignOrderReceiver;
use Sheba\SmsCampaign\Jobs\CampaignSmsStatusChangeJob;

class CampaignSmsStatusChanger
{
    public function processPendingSms()
    {
        foreach (SmsCampaignOrderReceiver::pending()->get() as $log) {
            dispatch(new CampaignSmsStatusChangeJob($log));
        }
    }
}