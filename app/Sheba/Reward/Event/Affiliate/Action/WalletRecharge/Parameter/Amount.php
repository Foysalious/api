<?php namespace Sheba\Reward\Event\Affiliate\Action\WalletRecharge\Parameter;

use App\Models\Payable;
use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Amount extends ActionEventParameter
{
    public function validate()
    {
         if (empty($this->value)) {
             throw new ParameterTypeMismatchException("Amount can't be empty");
         }
    }

    /**
     * @param array $params
     * @return bool
     */
    public function check(array $params)
    {
        if ($this->value == null) return true;

        /** @var Payable $payable */
        $payable = $params[1];
        return $payable->amount == $this->value;
    }
}