<?php namespace Sheba\Reward\Event\Partner\Action\PosOrderCreate\Parameter;

use App\Models\PosOrder;
use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Amount extends ActionEventParameter
{
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Amount can't be empty");
    }

    public function check(array $params)
    {


        /** @var PosOrder $order */
        $order = $params[1];
        if ($this->value != null) {
            return $order['net_bill'] >= $this->value;
        }

        return true;
    }
}