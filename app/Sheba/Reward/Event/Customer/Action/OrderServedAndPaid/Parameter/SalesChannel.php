<?php namespace Sheba\Reward\Event\Customer\Action\OrderServedAndPaid\Parameter;

use Sheba\Reward\Event\ActionEventParameter;
use DB;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class SalesChannel extends ActionEventParameter
{
    /**
     * @throws ParameterTypeMismatchException
     */
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Sales Channel can't be empty");
    }

    public function check(array $params)
    {
        $order = $params[0];
        if ($this->value != null) {
            return in_array($order->sales_channel, $this->value);
        }

        return true;
    }
}