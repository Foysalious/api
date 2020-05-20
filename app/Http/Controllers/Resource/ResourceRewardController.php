<?php namespace App\Http\Controllers\Resource;

use App\Models\Reward;
use App\Models\RewardLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Authentication\AuthUser;
use Sheba\Resource\Rewards\RewardHistory;
use Sheba\Resource\Rewards\RewardList;

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

        $rewards = $this->rewardList->setResource($resource)->get();

        return api_response($request, null, 200, ['campaigns' => $rewards['campaigns'], 'actions' => $rewards['actions']]);
    }

    public function history(Request $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();

        $logs = $this->rewardHistory->setResource($resource)->get();

        $history = [
            [
                "id" => 37594,
                "reward_id" => 36,
                "log" => "৫ টি সার্ভিসে ৫ স্টার",
                "created_at" => "2019-03-04 14:45:33",
                "reward" => [
                    "id" => 36,
                    "name" => "৫ স্টার বোনাস",
                    "type" => "Cash",
                    "detail_type" => 'Campaign',
                    "amount" => 100
                ],
                "progress" => [
                    "tag" => "rating",
                    "is_completed" => 0,
                    "target" => 5,
                    "completed" => 2
                ]
            ]
        ];
        return api_response($request, null, 200, ['reward_history' => $history]);
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
