<?php namespace Sheba\Reward;

use App\Models\Reward;
use Carbon\Carbon;
use Sheba\Reward\Disburse\DisburseHandler;

class ActionRewardDispatcher
{
    private $disburseHandler;

    /**
     * ActionRewardDispatcher constructor.
     * @param DisburseHandler $disburse_handler
     */
    public function __construct(DisburseHandler $disburse_handler)
    {
        $this->disburseHandler = $disburse_handler;
    }

    /**
     * @param $event
     * @param Rewardable $rewardable
     * @param mixed ...$params
     * @throws \Exception
     */
    public function run($event, Rewardable $rewardable, ...$params)
    {
        $published_rewards = Reward::with('detail')
            ->leftJoin('reward_actions', 'rewards.detail_id', '=', 'reward_actions.id')
            ->where('rewards.detail_type', 'App\Models\RewardAction')
            ->where('reward_actions.event_name', $event)
            ->where('rewards.start_time', '<=', Carbon::now())
            ->where('rewards.end_time', '>=', Carbon::now())
            ->select('rewards.*')
            ->get();

        foreach ($published_rewards as $reward) {
            /** @var Reward $reward */
            $event = $reward->setActionEvent($params)->actionEvent;

            if ($event->isEligible()) {
                $this->disburseHandler->setReward($reward)->disburse($rewardable);
            }
        }
    }
}