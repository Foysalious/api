<?php namespace Sheba\Reward\Event\Customer\Action\WalletCashback;

use Sheba\Reward\Event\ActionRule;

class Rule extends ActionRule
{
    public function validate()
    {
    }

    public function makeParamClasses()
    {
    }

    public function setValues()
    {
    }

    /**
     * @param array $params
     * @return bool
     */
    public function check(array $params)
    {
        return true;
    }
}