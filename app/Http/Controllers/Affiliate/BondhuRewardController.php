<?php


namespace App\Http\Controllers\Affiliate;


use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Reward;
use App\Models\RewardCampaign;
use Illuminate\Support\Facades\Artisan;
use Sheba\Dal\RewardAffiliates\Contract as RewardAffiliatesRepo;
use Sheba\Reward\CompletedCampaignHandler;

class BondhuRewardController extends Controller
{
    private $rewardAffiliateRepo;

    public function __construct(RewardAffiliatesRepo $rewardAffiliateRepo)
    {
        $this->rewardAffiliateRepo = $rewardAffiliateRepo;
    }

    public function rewardHistory($affiliate_id)
    {
        $affiliate_rewards = $this->getAffiliateRewards($affiliate_id);
        return $affiliate_rewards;
    }

    private function getAffiliateRewards($affiliate_id){

        $rewards_of_affiliate = $this->rewardAffiliateRepo->where('affiliate', $affiliate_id)->get();
        $rewards = Reward::whereIn('id', $rewards_of_affiliate->pluck('reward') )->get();

        foreach ( $rewards_of_affiliate as $each ) {
            $each->reward_details = $rewards->where('id', $each->reward)->values();
        }
        return $rewards_of_affiliate;
    }

}