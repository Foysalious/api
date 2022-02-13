<?php

namespace Sheba\Reward\Event\Partner\Action\TopUp\Parameter;

use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Operator extends ActionEventParameter
{
    public function validate()
    {
        if (empty($this->value)) throw new ParameterTypeMismatchException("Operators can't be empty");
    }

    /**
     * @param array $params
     * @return bool
     */
    public function check(array $params): bool
    {
        $topup_order = $params[0];
        if($this->value) {
            foreach ($this->value as $operator)
                if($operator == "All" || $operator == $topup_order->vendor->name) return true;

            return false;
        }

        return true;
    }
}