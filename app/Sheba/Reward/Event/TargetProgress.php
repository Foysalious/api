<?php namespace Sheba\Reward\Event;

class TargetProgress
{
    private $target;

    public function __construct(EventTarget $target)
    {
        $this->target = $target;
    }

    public function getAchieved()
    {
        return $this->target->getAchieved();
    }

    public function getTarget()
    {
        return $this->target->getTarget();
    }

    public function hasAchieved()
    {
        return $this->target->hasAchieved();
    }
}