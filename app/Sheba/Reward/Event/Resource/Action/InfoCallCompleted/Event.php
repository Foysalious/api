<?php namespace Sheba\Reward\Event\Resource\Action\InfoCallCompleted;


use Sheba\Reward\Event\Action;
use Sheba\Reward\AmountCalculator;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Event\Resource\Action\InfoCallCompleted\Rule;
use Sheba\Reward\Event\Rule as BaseRule;

class Event extends Action
{
    /**
     * @param BaseRule $rule
     * @return $this | Action
     * @throws RulesTypeMismatchException
     */
    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("InfoCall Completed event must have an order serve event rule");
        return parent::setRule($rule);
    }

    function isEligible()
    {
        return $this->rule->check($this->params);
    }

    function getLogEvent()
    {
        // TODO: Implement getLogEvent() method.
    }

}