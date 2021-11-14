<?php namespace App\Sheba\Business\Prorate;

class RunProrateOnActiveLeaveTypes
{
    private $business;

    public function setBusiness($business)
    {
        $this->business = $business;
        return $this;
    }

    public function run()
    {
        $leave_types = $this->business->leaveTypes;
        foreach ($leave_types as $leave_type){
            $auto_prorate_calculator = new AutoProrateCalculator();
            $auto_prorate_calculator->setBusiness($this->business)->setLeaveType($leave_type)->run();
        }
    }
}