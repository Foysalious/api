<?php namespace App\Sheba\Voucher;

use Sheba\Voucher\Contracts\CanApplyVoucher;

class PosCustomerWrapper implements CanApplyVoucher
{
    private $customer;


    /**
     * @param $customer
     * @return $this
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }


}