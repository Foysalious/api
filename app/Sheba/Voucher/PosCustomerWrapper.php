<?php namespace App\Sheba\Voucher;


class PosCustomerWrapper
{
    private $customer;
    /**
     * @var null
     */
    public $profile;

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