<?php namespace Sheba\Reward\Event\Partner\Action\WalletRecharge;

use Sheba\Reward\Event\Action;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Event\Rule as BaseRule;

class Event extends Action
{
    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Wallet recharge event must have a amount");

        return parent::setRule($rule);
    }

    public function isEligible()
    {
        return $this->rule->check($this->params);
    }
}