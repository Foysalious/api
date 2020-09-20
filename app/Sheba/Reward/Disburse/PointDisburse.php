<?php namespace Sheba\Reward\Disburse;

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
        $this->rewardable->update(['reward_point' => $new_reward_point]);
    }
}