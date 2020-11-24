<?php namespace Sheba\Reward\Event\Partner\Action\PaymentLinkUsage\Parameter;

use App\Models\Payable;
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
        /** @var Payable $payable */
        $payable = $params[1];
        if ($this->value != null) {
            return $payable->amount >= $this->value;
        }

        return true;
    }
}