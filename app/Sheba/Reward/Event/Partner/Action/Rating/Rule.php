<?php namespace Sheba\Reward\Event\Partner\Action\Rating;

use Sheba\Reward\Event\ActionRule;
use Sheba\Reward\Event\Partner\Action\Rating\Parameter\Rate;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Rule extends ActionRule
{
    /** @var Rate*/
    public $rate;

    /**
     * @throws ParameterTypeMismatchException
     */
    public function validate()
    {
        $this->rate->validate();
    }

    public function makeParamClasses()
    {
        $this->rate = new Rate();
    }

    public function setValues()
    {
        $this->rate->value = property_exists($this->rule, 'rate') ? (int) $this->rule->rate : null;
    }

    /**
     * @param array $params
     * @return bool
     * @throws ParameterTypeMismatchException
     */
    public function check(array $params)
    {
        return $this->rate->check($params);
    }
}