<?php namespace Sheba\Reward\Event\Resource\Action\InfoCallToOrderServedAndPaid;


use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Resource\Action\InfoCallToOrderServedAndPaid\Rule;
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