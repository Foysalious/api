<?php namespace Sheba\Business\PayrollSetting;

use App\Models\Business;

class Requester
{

    private $business;
    private $paymentSchedule;

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
}