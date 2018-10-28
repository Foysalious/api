<?php namespace Sheba\Reward\Event\Customer\Action\OrderServedAndPaid\Parameter;

use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class PaymentMethod extends ActionEventParameter
{
    /**
     * @throws ParameterTypeMismatchException
     */
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Payment method can't be empty");
    }

    /**
     * @param array $params
     * @return bool
     */
    public function check(array $params)
    {
        $order = $params[0];
        if ($this->value != null) {
            $order_payments = $order->lastJob()->partnerOrder->payments;

            if ($order_payments->isEmpty()) return false;

            $order_payment_methods = $order_payments->pluck('method')->toArray();
            return !empty(array_intersect(array_unique($order_payment_methods), $this->value));
        }

        return true;
    }
}