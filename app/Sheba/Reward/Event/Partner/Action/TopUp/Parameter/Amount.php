<?php

namespace Sheba\Reward\Event\Partner\Action\TopUp\Parameter;

use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Amount extends ActionEventParameter
{
    public function validate()
    {
         if (empty($this->value))
             throw new ParameterTypeMismatchException("Amount can't be empty");
    }

    /**
     * @param array $params
     * @return bool
     */
    public function check(array $params)
    {
        $topup_order = $params[0];
        if($topup_order->amount >= $this->value) return true;
        return false;
    }
}