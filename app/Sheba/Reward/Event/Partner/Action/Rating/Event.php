<?php namespace Sheba\Reward\Event\Partner\Action\Rating;

use Sheba\Reward\Event\Action;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Event\Rule as BaseRule;

class Event extends Action
{
    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Rating event must have a rate");

        return parent::setRule($rule);
    }

    public function isEligible()
    {
        return $this->rule->check($this->params);
    }
}