<?php namespace Sheba\Reward;

use App\Models\Reward;

use Carbon\Carbon;

use Exception;
use Sheba\Dal\RewardCampaignLog\RewardCampaignLogRepositoryInterface;
use Sheba\Reward\Disburse\DisburseHandler;
use Sheba\Reward\Event\Campaign;
use Sheba\Reward\Event\ParticipatedCampaignUser;
use Sheba\Reward\Helper\TimeFrameCalculator;

class CompletedCampaignHandler
{
    private $validRewards;
    private $timeFrame;
    private $disburseHandler;
    private $rewardCampaignLogRepository;


    public function __construct(DisburseHandler $disburse_handler, TimeFrameCalculator $timeframe_calculator, RewardCampaignLogRepositoryInterface $rewardCampaignLogRepository)
    {
        $this->timeFrame = $timeframe_calculator;
        $this->disburseHandler = $disburse_handler;
        $this->rewardCampaignLogRepository = $rewardCampaignLogRepository;
    }

    /**
     * @throws Exception
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
     * @throws Exception
     */
    private function campaignRulesCalculation()
    {
        /** @var Reward $reward */
        foreach ($this->validRewards as $reward) {
            $rewardable_users = collect();
            $events = $reward->setCampaignEvents()->campaignEvents;
            /** @var Campaign $event */
            foreach ($events as $event) {
                $participated_users = $event->getParticipatedUsers();
                foreach ($participated_users as $participated_user) {
                    $this->rewardCampaignLogRepository->create([
                        'reward_campaign_id' => $reward->detail_id,
                        'target_type' => $participated_user->getUserType(),
                        'target_id' => $participated_user->getUser()->id,
                        'achieved' => $participated_user->getAchievedValue()
                    ]);
                    if ($participated_user->getIsTargetAchieved()) $rewardable_users->push($participated_user->getUser());
                }

            }
            if (!$rewardable_users->isEmpty()) {
                $this->disburseRewardToUser($rewardable_users->unique('id'), $reward);
            }
        }
    }

    /**
     * @param $rewarded_users
     * @param $reward
     * @throws Exception
     */
    private function disburseRewardToUser($rewarded_users, $reward)
    {
        foreach ($rewarded_users as $rewardable) {
            $this->disburseHandler->setReward($reward)->disburse($rewardable);
        }
    }
}