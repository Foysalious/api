<?php


namespace App\Http\Controllers\Affiliate;


use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use Sheba\Dal\RewardAffiliates\Contract as RewardAffiliates;

class BondhuRewardController extends Controller
{
    private $rewardAffiliateRepo;
    public function __construct(RewardAffiliates $reward)
    {

    }

    public function rewardHistory(Affiliate $affiliate)
    {
        dd($affiliate);
    }
}