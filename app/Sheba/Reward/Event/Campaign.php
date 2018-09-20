<?php namespace Sheba\Reward\Event;

use Illuminate\Support\Collection;

use Sheba\Helpers\TimeFrame;
use Sheba\Reward\Event;
use Sheba\Reward\Rewardable;

abstract class Campaign extends Event
{
    /** @var TimeFrame */
    protected $timeFrame;

    public function setTimeFrame(TimeFrame $time_frame)
    {
        $this->timeFrame = $time_frame;
        return $this;
    }

    abstract function findRewardableUsers(Collection $users);

    abstract function checkProgress(Rewardable $rewardable);
}