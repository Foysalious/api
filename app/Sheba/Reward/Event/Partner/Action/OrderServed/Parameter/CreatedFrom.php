<?php namespace Sheba\Reward\Event\Partner\Action\OrderServed\Parameter;

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
        $order = $params[0];
        if ($this->value != null) {
            return in_array($order->portal_name, $this->value);
        }

        return true;
    }
}