<?php namespace App\Sheba\Business\Prorate;

use App\Models\BusinessMember;

class AutoProrateCalculator
{
    /** @var BusinessMember $business */
    private $business;
    private $leaveType;

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function setLeaveType($leave_type)
    {
        $this->leaveType = $leave_type;
        return $this;
    }

    public function run()
    {
        $active_business_members = $this->business->getActiveBusinessMember()->get();
        $fiscal_year = $this->business->getBusinessFiscalPeriod();
        foreach ($active_business_members as $business_member){
            $calculate_prorate = new CalculateProrate();
            $calculate_prorate
                ->setBusiness($this->business)
                ->setLeaveType($this->leaveType)
                ->setBusinessFiscalYear($fiscal_year)
                ->setBusinessMember($business_member)
                ->calculate();
        }
    }
}