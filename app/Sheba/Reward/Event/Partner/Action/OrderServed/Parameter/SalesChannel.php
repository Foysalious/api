<?php namespace Sheba\Reward\Event\Partner\Action\OrderServed\Parameter;

use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class SalesChannel extends ActionEventParameter
{
    /**
     * @throws ParameterTypeMismatchException
     */
    public function validate()
    {
        if(is_null($this->value)) return;

        if (empty($this->value))
            throw new ParameterTypeMismatchException("Sales Channels can't be empty");

        $sales_channels = getSalesChannels();
        foreach ($this->value as $value) {
            if(!in_array($value, $sales_channels))
                throw new ParameterTypeMismatchException("Sales Channel can't be " . $value);
        }
    }

    public function check(array $params)
    {
        $order = $params[0];
        if (!is_null($this->value)) {
            return in_array($order->sales_channel, $this->value);
        }

        return true;
    }
}