<?php

namespace Sheba\Reward\Event\Partner\Action\TopUp\Parameter;

use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class AmountGreaterThan extends ActionEventParameter
{
    public function validate(){}

    /**
     * @param array $params
     * @return bool
     */
    public function check(array $params): bool
    {
        $topup_order = $params[0];
        if($this->value) {
            if ($topup_order->amount > $this->value) return true;
            return false;
        }

        return true;
    }
}