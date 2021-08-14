<?php namespace App\Http\Controllers\PosRebuild;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\Reward\Event\Types;
use Sheba\Reward\RewardableTypes;

class RewardController extends Controller
{
    /**
     * @var ActionRewardDispatcher
     */
    private $actionRewardDispatcher;

    public function __construct(ActionRewardDispatcher $actionRewardDispatcher)
    {
        $this->actionRewardDispatcher = $actionRewardDispatcher;
    }

    public function actionReward(Request $request)
    {
        $this->validate($request, [
            'event' => 'required|in:' . implode(',', Types::get()),
            'rewardable_type' => 'required|in:' . implode(',', RewardableTypes::get()),
            'rewardable_id' => 'required|integer',
            'event_data' => 'required'
        ]);

        $model = "App\\Models\\" . ucfirst(camel_case($request->rewardable_type));
        $rewardable = $model::find((int)$request->rewardable_id);
        $event_data = json_encode($request->event_data);
        $this->actionRewardDispatcher->run($request->event, $rewardable, $rewardable, $event_data, $event_data->portal_name);

    }
}
