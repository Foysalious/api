<?php namespace Sheba\Reward\Event\Partner\Action\OrderServed;

use Sheba\Reward\Event\ActionRule;
use Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\ExcludedStatus;
use Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\Portal;
use Sheba\Reward\Event\Partner\Action\OrderServed\Parameter\Amount;

class Rule extends ActionRule
{
    /** @var ExcludedStatus */
    public $excludedStatus;
    /** @var Portal */
    public $portal;
    /** @var Amount */
    public $amount;

    /**
     * @throws \Sheba\Reward\Exception\ParameterTypeMismatchException
     */
    public function validate()
    {
        $this->excludedStatus->validate();
        $this->portal->validate();
        $this->amount->validate();
    }

    public function makeParamClasses()
    {
        $this->excludedStatus = new ExcludedStatus();
        $this->portal = new Portal();
        $this->amount = new Amount();
    }

    public function setValues()
    {
        $this->excludedStatus->value = property_exists($this->rule, 'excluded_status') ? $this->rule->excluded_status : null;
        $this->portal->value = property_exists($this->rule, 'portals') ? $this->rule->portals : null;
        $this->amount->value = property_exists($this->rule, 'amount') ? $this->rule->amount : null;
    }

    public function check(array $params)
    {
        return $this->excludedStatus->check($params) && $this->portal->check($params) && $this->amount->check($params);
    }
}