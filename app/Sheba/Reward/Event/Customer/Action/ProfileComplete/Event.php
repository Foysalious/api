<?php namespace Sheba\Reward\Event\Customer\Action\ProfileComplete;


use Sheba\Reward\Event\Action;
use Sheba\Reward\Event\Rule as BaseRule;
use Sheba\Reward\Exception\RulesTypeMismatchException;

class Event extends Action
{
    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("ProfileComplete event must have a ProfileComplete rule");

        return parent::setRule($rule);
    }

    public function getLogEvent()
    {
        // TODO: Implement getLogEvent() method.
    }

    public function isEligible()
    {
        return $this->rule->check($this->params);
    }
}