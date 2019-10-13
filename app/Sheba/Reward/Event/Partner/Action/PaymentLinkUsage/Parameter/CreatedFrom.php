<?php namespace Sheba\Reward\Event\Partner\Action\PaymentLinkUsage\Parameter;

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
        $partner_pos_service = $params[1];
        if ($this->value != null) {
            return in_array($partner_pos_service->created_by_name, $this->value);
        }

        return true;
    }
}