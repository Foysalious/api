<?php namespace Sheba\Reward\Event\Resource\Action\InfoCallCompleted\Parameter;


use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class CreatePortal extends ActionEventParameter
{
    /**
     * @throws ParameterTypeMismatchException
     */

    public function check(array $params)
    {
        $order = $params[0];
        return $this->value == null || $order->portal_name == $this->value;
    }

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Create portal can't be empty");
    }
}