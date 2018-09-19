<?php namespace Sheba\Reward\Event;

use App\Models\Reward;

use Illuminate\Support\Collection;

use Sheba\Helpers\TimeFrame;
use Sheba\Reward\Rewardable;

abstract class Campaign
{
    /** @var TimeFrame */
    protected $timeFrame;
    /** @var CampaignRule */
    protected $rule;
    /** @var Reward */
    protected $reward;

    public function setTimeFrame(TimeFrame $time_frame)
    {
        $this->timeFrame = $time_frame;
        return $this;
    }

    public function setRule(CampaignRule $rule)
    {
        $this->rule = $rule;
        return $this;
    }

    public function setReward(Reward $reward)
    {
        $this->reward = $reward;
        return $this;
    }

    abstract function findRewardableUsers(Collection $users);

    abstract function checkProgress(Rewardable $rewardable);
}