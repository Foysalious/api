<?php


namespace App\Http\Controllers\Affiliate;


use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Reward;
use App\Models\RewardCampaign;
use App\Sheba\Affiliate\AffiliateRewardHelper;
use Illuminate\Support\Facades\Artisan;
use Sheba\Dal\RewardAffiliates\Contract as RewardAffiliatesRepo;
use Sheba\Reward\CompletedCampaignHandler;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Reward\Event\Affiliate\RewardDetails;

class BondhuRewardController extends Controller
{
    private $rewardAffiliateRepo;
    private $affiliateRewardHelper;

    public function __construct(RewardAffiliatesRepo $rewardAffiliateRepo, AffiliateRewardHelper $affiliateRewardHelper )
    {
        $this->rewardAffiliateRepo = $rewardAffiliateRepo;
        $this->affiliateRewardHelper = $affiliateRewardHelper;
    }

    public function rewardHistory($affiliate, RewardDetails $rewardDetails)
    {
//        $affiliate_model = Affiliate::where('id', $affiliate)->get();
//        $rewards = $this->rewardAffiliateRepo->where('affiliate', $affiliate)->get();
        $affiliateRewards = $this->rewardAffiliateRepo->getRewardList($affiliate, Carbon::now(), '<');
        $affiliateRewards = $rewardDetails->mergeDetailsWithRewards($affiliateRewards);

        return $this->affiliateRewardHelper->checkRewardProgress($affiliateRewards);

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
        $affiliateRewards = collect($this->affiliateRewardHelper->checkRewardProgress($affiliateRewards))->sortByDesc('progress.percentage');

        return ['code' => 200, 'data' => $affiliateRewards];
    }

    /**
     * @param $affiliate
     * @param $rewardId
     * @param RewardDetails $rewardDetails
     * @return Reward|Reward[]|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     * todo: progress of the reward
     */
    public function rewardDetails($affiliate, $rewardId, RewardDetails $rewardDetails){
        $affiliateReward = $this->rewardAffiliateRepo->where('reward', $rewardId)
            ->where('affiliate', $affiliate)
            ->leftJoinReward()
            ->first();

        if ($affiliateReward){
            $affiliateReward = $rewardDetails->mergeDetailsWithReward($affiliateReward);
            $affiliateReward = $this->affiliateRewardHelper->checkRewardProgress([$affiliateReward]);
            return ['code' => 200, 'data' => $affiliateReward[0]];
        }
        return ['code' => 404, 'message' => 'Not found'];
    }

    public function getUnseenAchievedRewards($affiliate, RewardDetails $rewardDetails){
        $affiliateRewards = $this->rewardAffiliateRepo->getUnseenAchievedRewards($affiliate);
        $affiliateRewards = $rewardDetails->mergeDetailsWithRewards($affiliateRewards);

        return ['code' => 200, 'data' => $affiliateRewards];
    }

    public function updateIsSeen($affiliate, Request $request){
        if ($this->rewardAffiliateRepo->updateRewardSeen($request->affiliate_reward_id, true, $affiliate)){
            return ['code' => 200, 'message' => 'Successful'];
        }

        return ['code' => 400, 'message' => 'Something went wrong'];
    }

}