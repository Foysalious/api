<?php namespace App\Sheba\Business\Prorate;

use App\Models\BusinessMember;

class AutoProrateCalculator
{
    /** @var BusinessMember $business */
    private $business;

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function run()
    {
        $active_business_members = $this->business->getActiveBusinessMember()->get();
        $fiscal_year = $this->business->getBusinessFiscalPeriod();
        $leave_types = $this->business->leaveTypes;
        foreach ($active_business_members as $business_member){
            $calculate_prorate = new CalculateProrate();
            $calculate_prorate->setBusiness($this->business)->setBusinessFiscalYear($fiscal_year)->setBusinessMember($business_member);
            foreach ($leave_types as $leave_type){
                $calculate_prorate->setLeaveType($leave_type)->calculate();
            }
        }
    }
}