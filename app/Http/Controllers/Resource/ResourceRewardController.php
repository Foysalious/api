<?php namespace App\Http\Controllers\Resource;

use App\Models\Reward;
use App\Models\RewardLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;
use Sheba\Resource\Reward\RewardHistory;
use Sheba\Resource\Reward\RewardList;

class ResourceRewardController extends Controller
{
    private $rewardList;
    private $rewardHistory;
    /**
     * ResourceRewardController constructor.
     */
    public function __construct(RewardList $rewardList, RewardHistory $rewardHistory)
    {
        $this->rewardList = $rewardList;
        $this->rewardHistory = $rewardHistory;
    }

    public function index(Request $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();

        list($offset, $limit) = calculatePagination($request);

        $campaigns = $this->rewardList->setResource($resource)->setOffset($offset)->setLimit($limit)->getCampaigns();
        $actions = $this->rewardList->setResource($resource)->setOffset($offset)->setLimit($limit)->getActions();

        return api_response($request, null, 200, ['campaigns' => $campaigns, 'actions' => $actions]);
    }

    public function history(Request $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();

        $logs = $this->rewardHistory->setResource($resource)->get();

        return api_response($request, null, 200, ['reward_history' => $logs]);
    }

    public function show($reward, Request $request)
    {
        $info = [
            "id" => 2,
            "name" => "সিডিউল মাস্টার",
            "short_description" => "সিডিউল ডিউ ছাড়া সার্ভ করলেই ১৫,০০০ গিফ্‌ট পয়েন্ট",
            "type" => "Point",
            "amount" => 15000,
            "start_time" => "2018-10-03 00:00:00",
            "end_time" => "2018-10-09 23:59:59",
            "created_at" => "2018-10-03 11:47:36",
            "progress" => [
                "tag" => "order_serve",
                "is_completed" => 0,
                "target" => 5,
                "completed" => 2
            ],
            "rules" => [
                "নুন্যতম ১০ টি অর্ডার সিডিউল ডিউ ছাড়া সার্ভ করতে হবে",
                "শুধু মাত্র সার্ভ অর্ডার গণনার আন্তর্ভুক্ত হবে",
                "বিজয়ী গন আগামী ৯ অক্টোবর রাত ১২ টার পরে তাদের গিফ্‌ট পয়েন্ট বুঝে পাবেন।"
            ]
        ];
        return api_response($request, null, 200, ['info' => $info]);
    }
}
