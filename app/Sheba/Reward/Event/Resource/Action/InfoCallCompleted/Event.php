<?php namespace Sheba\Reward\Event\Resource\Action\InfoCallCompleted;


use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Resource\Action\InfoCallCompleted\Rule;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Event\Rule as BaseRule;

class Event extends Action
{
    public function setRule(BaseRule $rule)
    {
    }

    function isEligible()
    {
        // TODO: Implement isEligible() method.
    }

    function getLogEvent()
    {
        // TODO: Implement getLogEvent() method.
    }
}