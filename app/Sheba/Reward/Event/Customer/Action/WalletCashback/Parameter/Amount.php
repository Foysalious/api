<?php namespace Sheba\Reward\Event\Customer\Action\WalletCashback\Parameter;

use App\Models\PartnerOrderPayment;
use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Amount extends ActionEventParameter
{
    /**
     * @throws ParameterTypeMismatchException
     */
    public function validate()
    {
        if ((empty($this->value) && !is_numeric($this->value)) || is_null($this->value))
            throw new ParameterTypeMismatchException("Amount can't be empty");
    }

    /**
     * @param array $params
     * @return bool
     * @throws ParameterTypeMismatchException
     */
    public function check(array $params)
    {
        $payment = $params[0];
        if (!$payment instanceof PartnerOrderPayment) {
            throw new ParameterTypeMismatchException("First parameter is must be an instance of Partner Order Payment");
        }

        return $payment->amount >= $this->value;
    }
}