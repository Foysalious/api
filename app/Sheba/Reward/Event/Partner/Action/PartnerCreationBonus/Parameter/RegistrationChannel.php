<?php namespace Sheba\Reward\Event\Partner\Action\PartnerCreationBonus\Parameter;

use App\Models\Partner;
use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class RegistrationChannel extends ActionEventParameter
{
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Registration channel can't be empty");
    }

    public function check(array $params)
    {
        $partner = $params[0];
        if (!$partner instanceof Partner) {
            throw new ParameterTypeMismatchException("First parameter is must be an instance of Partner");
        }

        if ($this->value != null) {
            return in_array($partner->registration_channel, $this->value);
        }

        return true;
    }
}