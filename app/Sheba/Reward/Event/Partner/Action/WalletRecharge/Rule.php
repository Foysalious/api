<?php namespace Sheba\Reward\Event\Partner\Action\WalletRecharge;

use Sheba\Reward\Event\ActionRule;
use Sheba\Reward\Event\Partner\Action\WalletRecharge\Parameter\Amount;

class Rule extends ActionRule
{
    /** @var Amount */
    public $amount;

    /**
     * @throws \Sheba\Reward\Exception\ParameterTypeMismatchException
     */
    public function validate()
    {
        $this->amount->validate();
    }

    public function makeParamClasses()
    {
        $this->amount = new Amount();
    }

    public function setValues()
    {
        $this->amount->value = property_exists($this->rule, 'amount') ? (int) $this->rule->amount : null;
    }

    /**
     * @param array $params
     * @return bool
     */
    public function check(array $params)
    {
        return $this->amount->check($params);
    }
}