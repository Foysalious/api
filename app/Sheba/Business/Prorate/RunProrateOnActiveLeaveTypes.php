<?php namespace App\Sheba\Business\Prorate;

class RunProrateOnActiveLeaveTypes
{
    private $business;
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

    public function run()
    {
        $leave_types = $this->business->leaveTypes;
        foreach ($leave_types as $leave_type){
            $auto_prorate_calculator = new AutoProrateCalculator();
            $auto_prorate_calculator->setBusiness($this->business)->setProrateType($this->prorateType)->setLeaveType($leave_type)->run();
        }
    }
}