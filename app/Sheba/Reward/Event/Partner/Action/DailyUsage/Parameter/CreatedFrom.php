<?php namespace Sheba\Reward\Event\Partner\Action\DailyUsage\Parameter;

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
            throw new ParameterTypeMismatchException("Requested From can't be empty");
    }

    public function check(array $params)
    {
        $requested_from = $params[1];
        if ($this->value != null) {
            return in_array($requested_from, $this->value);
        }

        return true;
    }
}