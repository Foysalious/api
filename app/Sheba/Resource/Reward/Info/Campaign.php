<?php namespace Sheba\Resource\Reward\Info;

use Sheba\Reward\CampaignEventInitiator;

class Campaign extends Info
{
    private $eventInitiator;

    public function __construct(CampaignEventInitiator $event_initiator)
    {
        $this->eventInitiator = $event_initiator;
    }

    protected function getProgress()
    {
        $progress = [];
        foreach (json_decode($this->reward->detail->events) as $key => $event) {
            $event = $this->eventInitiator->setReward($this->reward)->setName($key)->setRule($event)->initiate();
            $target_progress = $event->checkProgress($this->rewardable);
            array_push($progress, array(
                'tag' => $key,
                'target' => $target_progress->getTarget(),
                'completed' => $target_progress->getAchieved(),
                'is_completed' => $target_progress->hasAchieved() ? 1 : 0
            ));
        }
        return $progress;
    }
}