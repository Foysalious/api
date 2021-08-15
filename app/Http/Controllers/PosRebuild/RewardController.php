<?php namespace App\Http\Controllers\PosRebuild;

use Illuminate\Http\Request;

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
        $event_data = is_string($request->event_data) ? json_decode($request->event_data) : $request->event_data;
        $data = $request->event == Types::POS_ORDER_CREATE ? json_decode(json_encode($event_data), true) : $event_data;
        if (isset($event_data->portal_name)) {
            $this->actionRewardDispatcher->run($request->event, $rewardable, $rewardable, $data, $event_data->portal_name);
        }
        return http_response($request, null, 200);
    }
}
