<?php namespace Sheba\Reward\Event\Affiliate\Action\TopUp\Parameter;

use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class FixedAmount extends ActionEventParameter
{
    public function validate(){}

    /**
     * @param array $params
     * @return bool
     */
    public function check(array $params): bool
    {
        if (is_null($this->value)) return true;

        $topup_order = $params[0];
        return $topup_order->amount == $this->value;
    }
}