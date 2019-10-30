<?php namespace Sheba\Reward\Event\Partner\Action\PartnerCreationBonus;

use Sheba\Reward\Event\ActionRule;
use Sheba\Reward\Event\Partner\Action\PartnerCreationBonus\Parameter\RegistrationChannel;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Rule extends ActionRule
{
    /** @var RegistrationChannel*/
    public $registrationChannel;

    /**
     * @throws ParameterTypeMismatchException
     */
    public function validate()
    {
        $this->registrationChannel->validate();
    }

    public function makeParamClasses()
    {
        $this->registrationChannel = new RegistrationChannel();
    }

    public function setValues()
    {
        $this->registrationChannel->value = property_exists($this->rule, 'registration_channel') ? $this->rule->registration_channel : null;
    }

    /**
     * @param array $params
     * @return bool
     * @throws ParameterTypeMismatchException
     */
    public function check(array $params)
    {
        return $this->registrationChannel->check($params);
    }
}