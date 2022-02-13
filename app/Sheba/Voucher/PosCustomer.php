<?php namespace App\Sheba\Voucher;

use Sheba\Voucher\Contracts\CanApplyVoucher;

class PosCustomer implements CanApplyVoucher
{
    public $mobile;
    public  $profile;
    public  $movieTicketOrders;
    public  $id;


    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }


    public function setProfile($profile = null)
    {
        $this->profile = $profile;
        return $this;
    }


    public function setMovieTicketOrders($movieTicketOrders)
    {
        $this->movieTicketOrders = $movieTicketOrders;
        return $this;
    }


    public function setId($id = null)
    {
        $this->id = $id;
        return $this;
    }


}