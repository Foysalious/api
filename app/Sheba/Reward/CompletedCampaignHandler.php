<?php namespace Sheba\Reward;

use App\Models\Reward;
use Carbon\Carbon;

class CompletedCampaignHandler
{
    private $validRewards;
    private $timeFrame;
    private $disburseHandler;
    private $eventInitiator;

    public function __construct(DisburseHandler $disburse_handler, CampaignEventInitiator $event_initiator, TimeFrameCalculator $timeframe_calculator)
    {
        $this->timeFrame = $timeframe_calculator;
        $this->disburseHandler = $disburse_handler;
        $this->eventInitiator = $event_initiator;
    }

    public function run()
    {
        $this->validRewardFinder();
        $this->campaignRulesCalculation();
    }

    private function validRewardFinder()
    {
        $published_rewards = Reward::with('detail', 'constraints.constraint')
            ->where('end_time', '>=', Carbon::yesterday())
            ->where('detail_type', 'App\Models\RewardCampaign')
            ->get();

        $this->validRewards = $published_rewards->filter(function ($reward) {
            return $this->timeFrame->setReward($reward)->isValid();
        });
    }

    public function campaignRulesCalculation()
    {
        foreach ($this->validRewards as $reward) {
            $rewardable_users = null;

            foreach (json_decode($reward->detail->events) as $event_name => $event_rule) {
                $event = $this->eventInitiator->setReward($reward)->setName($event_name)->setRule($event_rule)
                    ->initiate($event_name, $event_rule);
                $rewardable_users = $event->findRewardableUsers($rewardable_users);
            }

            if (!$rewardable_users->isEmpty()) {
                $this->disburseRewardToUser($rewardable_users, $reward);
            }
        }
    }

    /**
     * @param $rewarded_users
     * @param $reward
     */
    public function disburseRewardToUser($rewarded_users, $reward)
    {
        foreach ($rewarded_users as $rewardable) {
            $this->disburseHandler->setReward($reward)->disburse($rewardable);
        }
    }
}