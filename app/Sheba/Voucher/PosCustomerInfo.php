<?php namespace App\Sheba\Voucher;

use App\Models\PosCustomer;
use Sheba\Voucher\Contracts\CanApplyVoucher;

class PosCustomerInfo implements CanApplyVoucher
{

    private $customer;
    /**
     * @var null
     */

    private $mobile;

    public $profile;
    public $movieTicketOrders;


    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return PosCustomer
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