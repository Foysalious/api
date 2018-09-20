<?php namespace Sheba\Reward;

use App\Models\Reward;
use Carbon\Carbon;

class ActionRewardDispatcher
{
    private $disburseHandler;
    private $eventInitiator;

    public function __construct(DisburseHandler $disburse_handler, ActionEventInitiator $event_initiator)
    {
        $this->disburseHandler = $disburse_handler;
        $this->eventInitiator = $event_initiator;
    }

    public function run($event, $rewardables, ...$params)
    {
        $published_rewards = Reward::with('detail')
            ->leftJoin('reward_actions', 'rewards.detail_id', '=', 'reward_actions.id')
            ->where('rewards.detail_type', 'App\Models\RewardAction')
            ->where('reward_actions.event_name', $event)
            ->where('rewards.start_time', '<=', Carbon::now())
            ->where('rewards.end_time', '>=', Carbon::now())
            ->get();

        foreach ($published_rewards as $reward) {

            $event_name = $reward->detail->event_name;
            $event_rule = json_decode($reward->detail->event_rules);
            $event = $this->eventInitiator->setReward($reward)->setName($event_name)->setRule($event_rule)->initiate();

            if ($event->isEligible($params)) {
                // $rewarded_user = $rewardables[$reward->target_type];
                // $this->disburseRewardToUser($rewarded_user, $reward);
            }
        }
    }

    private function disburseRewardToUser($rewardable, $reward)
    {
        if ($reward->detail_type == constants('REWARD_TYPE')['Cash']) {
            $rewardable->update(['wallet' => floatval($rewardable->wallet) + floatval($reward->amount)]);
        } elseif ($reward->detail_type == constants('REWARD_TYPE')['Point']) {
            $rewardable->update(['reward_point' => floatval($rewardable->reward_point) + floatval($reward->amount)]);
        }

        //$log = "Rewarded $reward->amount " . $reward->type;
        //$this->rewardRepo->storeLog($reward, $rewardableId, $log);
    }
}