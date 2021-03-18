<?php


namespace App\Http\Controllers\Affiliate;


use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Reward;
use App\Models\RewardCampaign;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Dal\RewardAffiliates\Contract as RewardAffiliatesRepo;
use Sheba\Reward\Event\Affiliate\RewardDetails;

class BondhuRewardController extends Controller
{
    private $rewardAffiliateRepo;
    public function __construct(RewardAffiliatesRepo $rewardAffiliateRepo)
    {
        $this->rewardAffiliateRepo = $rewardAffiliateRepo;
    }

    public function rewardHistory($affiliate, RewardDetails $rewardDetails)
    {
//        $affiliate_model = Affiliate::where('id', $affiliate)->get();
//        $rewards = $this->rewardAffiliateRepo->where('affiliate', $affiliate)->get();
        $affiliateRewards = $this->rewardAffiliateRepo->getRewardList($affiliate, Carbon::now(), '<=');
        $affiliateRewards = $rewardDetails->mergeDetailsWithRewards($affiliateRewards);

    }

    /**
     * @param $affiliate
     * @param RewardDetails $rewardDetails
     * @return mixed
     * todo: progress of the reward
     */
    public function rewardList($affiliate, RewardDetails $rewardDetails){
        $affiliateRewards = $this->rewardAffiliateRepo->getRewardList($affiliate, Carbon::now(), '>', Carbon::now(), '<=');
        $affiliateRewards = $rewardDetails->mergeDetailsWithRewards($affiliateRewards);

//        $minStartTime = $affiliateRewards->min('start_time');
//        $maxEndTime = $affiliateRewards->max('end_time');

        return $affiliateRewards;
    }

    /**
     * @param $affiliate
     * @param $rewardId
     * @return Reward|Reward[]|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     * todo: progress of the reward
     */
    public function rewardDetails($affiliate, $rewardId){
        return Reward::with('detail')->find($rewardId);
    }

    public function getUnseenAchievedRewards($affiliate, RewardDetails $rewardDetails){
        $affiliateRewards = $this->rewardAffiliateRepo->getUnseenAchievedRewards($affiliate);
        $affiliateRewards = $rewardDetails->mergeDetailsWithRewards($affiliateRewards);

        return $affiliateRewards;
    }

    public function updateIsSeen($affiliate, Request $request){
        if ($this->rewardAffiliateRepo->updateRewardSeen($request->affiliate_reward_id, true, $affiliate)){
            return ['code' => 200, 'message' => 'Successful'];
        }

        return ['code' => 400, 'message' => 'Something went wrong'];
    }
}