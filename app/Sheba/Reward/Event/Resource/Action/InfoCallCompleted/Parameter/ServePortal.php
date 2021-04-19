<?php namespace Sheba\Reward\Event\Resource\Action\InfoCallCompleted\Parameter;

use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class ServePortal extends ActionEventParameter
{
    /**
     * @throws ParameterTypeMismatchException
     */

    public function check(array $params)
    {
        $order = $params[0];
        if ($this->value != null) {

        }

        return true;
    }

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Serve Portal can't be empty");
    }
}