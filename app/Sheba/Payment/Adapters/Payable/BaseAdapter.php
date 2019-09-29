<?php namespace Sheba\Payment\Adapters\Payable;


abstract class BaseAdapter
{
    protected $paymentMethod;

    /**
     * @param $method
     * @return $this
     */
    public function setPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        return $this;
    }
}