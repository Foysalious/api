<?php namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;
use Sheba\Resource\Reward\RewardHistory;
use Sheba\Resource\Reward\RewardList;

class ResourceRewardController extends Controller
{
    public function index(Request $request, RewardList $rewardList)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();

        list($offset, $limit) = calculatePagination($request);

        $campaigns = $rewardList->setResource($resource)->setOffset($offset)->setLimit($limit)->getCampaigns();
        $actions = $rewardList->setResource($resource)->setOffset($offset)->setLimit($limit)->getActions();

        return api_response($request, null, 200, ['campaigns' => $campaigns, 'actions' => $actions]);
    }

    public function history(Request $request, RewardHistory $rewardHistory)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();

        $logs = $rewardHistory->setResource($resource)->get();

        if(empty($logs)) return api_response($request, null, 404);

        return api_response($request, null, 200, ['reward_history' => $logs]);
    }
}
