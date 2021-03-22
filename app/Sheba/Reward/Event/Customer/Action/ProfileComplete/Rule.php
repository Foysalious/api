<?php


namespace Sheba\Reward\Event\Customer\Action\ProfileComplete;

use Sheba\Reward\Event\ActionRule;
use Sheba\Reward\Event\Customer\Action\ProfileComplete\Parameter\ProfileComplete;

class Rule extends ActionRule
{
    /** @var ProfileComplete */
    public $profileComplete;
    /**
     * @throws \Sheba\Reward\Exception\ParameterTypeMismatchException
     */
    public function validate()
    {
        $this->profileComplete->validate();
    }

    public function makeParamClasses()
    {
        $this->profileComplete = new ProfileComplete();
    }

    public function setValues()
    {
        $this->profileComplete->value = property_exists($this->rule, 'profileComplete') ? $this->rule->profileComplete : null;
    }

    public function check(array $params)
    {
        return $this->profileComplete->check($params);
    }

}