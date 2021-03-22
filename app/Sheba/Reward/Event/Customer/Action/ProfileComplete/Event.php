<?php


namespace Sheba\Reward\Event\Customer\Action\ProfileComplete;


use Sheba\Reward\Event\Action;

class Event extends Action
{
    private $rewardAmount;

    public function setParams(array $params)
    {
        parent::setParams($params);
    }

    public function isEligible()
    {
        // TODO: Implement isEligible() method.
    }

    public function getLogEvent()
    {
        // TODO: Implement getLogEvent() method.
    }
}