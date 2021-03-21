<?php


namespace App\Sheba\Affiliate;


use App\Models\Affiliate;
use App\Models\Reward;
use Sheba\Dal\AffiliateNotificationLogs\Contract as AffiliateNotificationLogsRepository;
use Sheba\Dal\RewardAffiliates\Contract as RewardAffiliatesRepo;
use Sheba\ModificationFields;

class AffiliateRewardHelper
{
    private $rewardAffiliatesRepo;
    use ModificationFields;

    public function __construct(RewardAffiliatesRepo $rewardAffiliatesRepo)
    {
        $this->rewardAffiliatesRepo = $rewardAffiliatesRepo;
    }

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



    public function setRewardAchieved($affiliate, $reward)
    {
        $reward_affiliate = $this->rewardAffiliatesRepo->where('reward', $reward->id)->where('affiliate', $affiliate->id)->get();
        if($reward_affiliate->count() > 0){
            $this->rewardAffiliatesRepo->update($reward_affiliate[0], ['is_achieved' => true ]);
        }
    }

    public function getAchievedRewardsId( $affiliate )
    {
        $rewards =  $this->rewardAffiliatesRepo->where('affiliate', $affiliate )->where('is_achieved', 1 )->get();
        return $rewards->count() > 0 ?  array_column($rewards->toArray(), 'reward') : [];
    }

}