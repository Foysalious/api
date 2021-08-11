<?php namespace App\Sheba\Voucher;


class PosCustomerInfo
{
    private $customer;
    /**
     * @var null
     */

    private $mobile;

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

    /**
     * @param null $mobile
     * @return PosCustomerInfo
     */
    public function setCustomerMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }
    public function getCustomerMobile()
    {
        return $this->mobile ;

    }

}