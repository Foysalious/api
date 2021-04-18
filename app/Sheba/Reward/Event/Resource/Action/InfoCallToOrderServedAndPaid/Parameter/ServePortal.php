<?php namespace Sheba\Reward\Event\Resource\Action\InfoCallToOrderServedAndPaid\Parameter;

use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class ServePortal extends ActionEventParameter
{
    /**
     * @throws ParameterTypeMismatchException
     */

    public function check(array $params)
    {
        // TODO: Implement check() method.
    }

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Portal can't be empty");
    }
}