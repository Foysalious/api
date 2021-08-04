<?php namespace Sheba\Reward\Event\Resource\Action\InfoCallCompleted\Parameter;

use Sheba\Reward\Event\ActionEventParameter;

class Amount extends ActionEventParameter
{
    public function check(array $params)
    {
        $order = $params[0];
        if ($this->value != null) {
            return $order->calculate(true)->totalPrice >= $this->value;
        }
        return true;
    }

    public function validate()
    {
        // TODO: Implement validate() method.
    }
}