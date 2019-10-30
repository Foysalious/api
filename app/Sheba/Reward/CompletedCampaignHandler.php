<?php namespace Sheba\Reward;

use App\Models\Reward;

use Carbon\Carbon;

use Sheba\Reward\Disburse\DisburseHandler;
use Sheba\Reward\Helper\TimeFrameCalculator;

class CompletedCampaignHandler
{
    private $validRewards;
    private $timeFrame;
    private $disburseHandler;

    /**
     * CompletedCampaignHandler constructor.
     * @param DisburseHandler $disburse_handler
     * @param TimeFrameCalculator $timeframe_calculator
     */
    public function __construct(DisburseHandler $disburse_handler, TimeFrameCalculator $timeframe_calculator)
    {
        $this->timeFrame = $timeframe_calculator;
        $this->disburseHandler = $disburse_handler;
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        $this->findAndSetValidReward();
        $this->campaignRulesCalculation();
    }

    private function findAndSetValidReward()
    {
        $published_rewards = Reward::with('detail', 'constraints.constraint')
            ->where('end_time', '>=', Carbon::yesterday())
            ->where('detail_type', 'App\Models\RewardCampaign')
            ->get();


        $this->validRewards = $published_rewards->filter(function ($reward) {
            return $this->timeFrame->setReward($reward)->isValid();
        });
    }

    /**
     * @throws \Exception
     */
    private function campaignRulesCalculation()
    {
        foreach ($this->validRewards as $reward) {
            /** @var Reward $reward */
            $rewardable_users = null;
            $events = $reward->setCampaignEvents()->campaignEvents;

            foreach ($events as $event) {
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
     * @throws \Exception
     */
    private function disburseRewardToUser($rewarded_users, $reward)
    {
        foreach ($rewarded_users as $rewardable) {
            $this->disburseHandler->setReward($reward)->disburse($rewardable);
        }
    }
}