<?php namespace Sheba\Business\PayrollSetting;

use App\Models\Business;

class Requester
{

    private $business;

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    public function getBusiness()
    {
        return $this->business;
    }
}