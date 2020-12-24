<?php namespace Sheba\Reward\Disburse;

use Sheba\Repositories\RewardPointLogRepository;
use Sheba\Reward\Rewardable;

class PointDisburse
{
    /** @var Rewardable */
    private $rewardable;

    public function setRewardable(Rewardable $rewardable)
    {
        $this->rewardable = $rewardable;
        return $this;
    }

    public function updateRewardPoint($point)
    {
        $new_reward_point = $this->rewardable->reward_point + $point;
        (new RewardPointLogRepository())->storeInLog($this->rewardable, $point, "$point Point incremented for reward");
        $this->rewardable->update(['reward_point' => $new_reward_point]);
    }
}