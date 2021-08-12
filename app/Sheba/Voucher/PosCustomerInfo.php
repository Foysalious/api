<?php namespace App\Sheba\Voucher;


use App\Models\PosCustomer;

class PosCustomerInfo
{
    /**
     * @var PosCustomer
     */
    private $customer;
    /**
     * @var null
     */

    private $mobile;

    /**
     * @param $customer
     * @return $this
     */
    public function setCustomer(PosCustomer $customer): PosCustomerInfo
    {
        $this->customer = $customer;
        return $this;
    }


    /**
     * @return PosCustomer
     */
    public function getCustomer(): PosCustomer
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