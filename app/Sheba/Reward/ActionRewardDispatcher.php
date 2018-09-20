<?php namespace Sheba\Reward;

use App\Models\Reward;
use Carbon\Carbon;

class ActionRewardDispatcher
{
    private $disburseHandler;
    private $eventInitiator;

    /**
     * ActionRewardDispatcher constructor.
     * @param DisburseHandler $disburse_handler
     * @param ActionEventInitiator $event_initiator
     */
    public function __construct(DisburseHandler $disburse_handler, ActionEventInitiator $event_initiator)
    {
        $this->disburseHandler = $disburse_handler;
        $this->eventInitiator = $event_initiator;
    }

    public function run($event, Rewardable $rewardable, ...$params)
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
                $this->disburseHandler->setReward($reward)->disburse($rewardable);
            }
        }
    }
}