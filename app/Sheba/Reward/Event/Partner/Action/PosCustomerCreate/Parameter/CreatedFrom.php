<?php namespace Sheba\Reward\Event\Partner\Action\PosCustomerCreate\Parameter;

use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class CreatedFrom extends ActionEventParameter
{
    /**
     * @throws ParameterTypeMismatchException
     */
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Created from can't be empty");
    }

    public function check(array $params)
    {
        $partner_pos_customer = $params[1];
        if ($this->value != null) {
            return in_array($partner_pos_customer->portal_name, $this->value);
        }

        return true;
    }
}