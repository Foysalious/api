<?php namespace Sheba\Reward;

use App\Models\Reward;
use Carbon\Carbon;

class ActionRewardDispatcher
{
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
            $event_class = (new EventDataConverter())->getClass($reward->target_type, $reward->detail_type, $event);
            if ((new $event_class())->isEligible($reward->detail->event_rules, ...$params)) {
                $rewarded_user = $rewardables[$reward->target_type];
                $this->disburseRewardToUser($rewarded_user, $reward);
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