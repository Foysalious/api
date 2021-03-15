<?php


namespace App\Http\Controllers\Affiliate;


use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use Sheba\Dal\RewardAffiliates\Contract as RewardAffiliatesRepo;

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
}