<?php namespace Sheba\Business\PayrollSetting;

use App\Models\Business;

class Requester
{

    private $business;
    private $paymentSchedule;
    private $isEnable;
    private $payDayType;
    private $payDay;

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function getBusiness()
    {
        return $this->business;
    }

    public function setPaymentSchedule($payment_schedule)
    {
        $this->paymentSchedule = $payment_schedule;
        return $this;
    }

    public function getPaymentSchedule()
    {
        return $this->paymentSchedule;
    }

    public function setIsEnable($is_enable)
    {
        $this->isEnable = $is_enable;
        return $this;
    }

    public function getIsEnable()
    {
        return $this->isEnable;
    }

    public function setPayDayType($pay_day_type)
    {
        $this->payDayType = $pay_day_type;
        return $this;
    }

    public function getPayDayType()
    {
        return $this->payDayType;
    }

    public function setPayDay($pay_day)
    {
        $this->payDay = $pay_day;
        return $this;
    }

    public function getPayDay()
    {
        return $this->payDay;
    }
}