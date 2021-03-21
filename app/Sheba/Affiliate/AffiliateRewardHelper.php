<?php


namespace App\Sheba\Affiliate;


use App\Models\Affiliate;
use App\Models\Reward;

class AffiliateRewardHelper
{

    public function checkRewardProgress( $affiliate_rewards )
    {
        $affiliate_progress = [];
        foreach ($affiliate_rewards as $key=>$each_reward ) {
            $reward_model = Reward::find($each_reward->reward);
            $event = $reward_model->setCampaignEvents()->campaignEvents;
            $progress = $event[0]->checkProgress(Affiliate::find($each_reward->affiliate));
            $temp = $each_reward->toArray();
            $temp['progress'] = $progress;
            $affiliate_progress [] = $temp;
        }
        return $affiliate_progress;
    }
}