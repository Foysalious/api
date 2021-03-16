<?php


namespace App\Http\Controllers\Affiliate;


use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\RewardCampaign;
use Carbon\Carbon;
use Sheba\Dal\RewardAffiliates\Contract as RewardAffiliatesRepo;
use Sheba\Reward\Event\Affiliate\RewardDetails;

class BondhuRewardController extends Controller
{
    private $rewardAffiliateRepo;
    public function __construct(RewardAffiliatesRepo $rewardAffiliateRepo)
    {
        $this->rewardAffiliateRepo = $rewardAffiliateRepo;
    }

    public function rewardHistory($affiliate)
    {
//        $affiliate_model = Affiliate::where('id', $affiliate)->get();
        $rewards = $this->rewardAffiliateRepo->where('affiliate', $affiliate)->get();

    }

    public function rewardList($affiliate, RewardDetails $rewardDetails){
        $affiliateRewards = $this->rewardAffiliateRepo->where('affiliate', $affiliate)
            ->leftJoinReward()
            ->where('rewards.start_time', '<=', Carbon::now())
            ->where('rewards.end_time', '>', Carbon::now())
            ->get();

        $affiliateRewards = $rewardDetails->mergeDetailsWithRewards($affiliateRewards);

        return $affiliateRewards;
    }

    public function rewardDetails($rewardId){

    }
}