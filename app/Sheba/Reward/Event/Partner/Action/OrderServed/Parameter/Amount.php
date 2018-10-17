<?php namespace Sheba\Reward\Event\Partner\Action\OrderServed\Parameter;

use Sheba\Reward\Event\ActionEventParameter;

class Amount extends ActionEventParameter
{
    public function validate(){}

    public function check(array $params)
    {
        $order = $params[0];
        if ($this->value != null) {
            return $order->calculate(true)->totalPrice >= $this->value;
        }

        return true;
    }
}