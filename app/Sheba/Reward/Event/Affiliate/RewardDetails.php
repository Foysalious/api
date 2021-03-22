<?php


namespace Sheba\Reward\Event\Affiliate;


use App\Models\RewardAction;
use App\Models\RewardCampaign;

class RewardDetails
{
    /**
     * @param $affiliateRewards (this is left joined with rewards table)
     * @return mixed
     * This method merges affiliate rewards with reward_campaigns and reward_actions.
     */
    public function mergeDetailsWithRewards($affiliateRewards){
        $rewardCampaigns = $this->getCampaignDetails($affiliateRewards);
        $rewardActions = $this->getActionDetails($affiliateRewards);

        foreach ($affiliateRewards as $reward){
            if ($reward->detail_type == 'App\Models\RewardCampaign'){
                $rewardDetails = $rewardCampaigns->where('id', $reward->detail_id)->first();
            } else {
                $rewardDetails = $rewardActions->where('id', $reward->detail_id)->first();
            }
            $rewardDetails->events = json_decode($rewardDetails->events);
            $reward['details'] = $rewardDetails;
        }

        return $affiliateRewards;
    }

    public function mergeDetailsWithReward($affiliateReward){
        if ($affiliateReward->detail_type == 'App\Models\RewardCampaign'){
            $rewardDetails = RewardCampaign::where('id', $affiliateReward->detail_id)->first();
        } else {
            $rewardDetails = RewardAction::where('id', $affiliateReward->detail_id)->first();
        }
        $rewardDetails->events = json_decode($rewardDetails->events);
        $affiliateReward['details'] = $rewardDetails;
        return $affiliateReward;
    }

    private function getCampaignDetails($rewards){
        $campaigns = $rewards->where('detail_type', 'App\Models\RewardCampaign')->pluck('detail_id');
        return RewardCampaign::whereIn('id', $campaigns)->get();
    }

    private function getActionDetails($rewards){
        $actions = $rewards->where('detail_type', 'App\Models\RewardAction')->pluck('detail_id');
        return RewardAction::whereIn('id', $actions)->get();
    }
}