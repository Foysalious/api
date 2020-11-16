<?php namespace App\Http\Controllers\Resource\Reward;

use App\Http\Controllers\Controller;
use App\Models\RewardCampaign;
use Illuminate\Http\Request;
use Sheba\Authentication\AuthUser;
use Sheba\Resource\Reward\Info\Campaign;

class CampaignController extends Controller
{
    public function show($campaign, Request $request, Campaign $campaign_info)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $campaign = RewardCampaign::find($campaign);
        if (!$campaign) return api_response($request, null, 404);
        $campaign_info->setReward($campaign->reward)->setRewardable($resource);
        return api_response($request, null, 200, ['info' => $campaign_info->getInfo()]);
    }
}