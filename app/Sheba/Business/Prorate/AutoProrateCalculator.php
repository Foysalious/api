<?php namespace App\Sheba\Business\Prorate;

use App\Models\Business;

class AutoProrateCalculator
{
    /** @var Business $business */
    private $business;
    private $leaveType;
    private $prorateType;

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function setProrateType($prorate_type)
    {
        $this->prorateType = $prorate_type;
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
                ->setProrateType($this->prorateType)
                ->setBusinessFiscalYear($fiscal_year)
                ->setBusinessMember($business_member)
                ->calculate();
        }
    }
}