<?php namespace Sheba\Reward;

use App\Models\Reward;
use App\Models\RewardAction;
use Carbon\Carbon;
use Exception;
use Sheba\Reward\Disburse\DisburseHandler;
use Sheba\Reward\Event\Action;

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
     * @throws Exception
     */
    public function run($event, Rewardable $rewardable, ...$params)
    {
        $published_rewards = $this->getPublishedRewards($event, $rewardable);

        if ($event == 'info_call_completed') {
            $this->disburseHandler->setPartnerOrder($params[0]);
        }

        foreach ($published_rewards as $reward) {
            /**
             * @var Reward $reward
             * @var Action $event
             */
            $event = $reward->setActionEvent($params)->actionEvent;

            if ($event->isEligible()) {
                $this->disburseHandler->setReward($reward)->setEvent($event)->disburse($rewardable);
            }
        }
    }

    private function getPublishedRewards($event, Rewardable $rewardable)
    {
        $now = Carbon::now();

        return Reward::with('detail')
            ->leftJoin('reward_actions', 'rewards.detail_id', '=', 'reward_actions.id')
            ->where('rewards.detail_type', RewardAction::class)
            ->where('reward_actions.event_name', $event)
            ->where('rewards.target_type', get_class($rewardable))
            ->where('rewards.start_time', '<=', $now)
            ->where('rewards.end_time', '>=', $now)
            ->select('rewards.*')
            ->get();
    }
}
