<?php namespace App\Sheba\Business\SalaryLog\ComponentBreakdownLog;

class Requester
{
    private $businessMember;
    private $componentTitle;
    private $componentPercentage;
    private $componentAmount;
    private $salary;
    private $oldPercentage;
    private $oldAmount;
    private $managerMember;

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setSalary($salary)
    {
        $this->salary = $salary;
        return $this;
    }

    public function getSalary()
    {
        return $this->salary;
    }

    public function setComponentTitle($component_title)
    {
        $this->componentTitle = $component_title;
        return $this;
    }

    public function getComponentTitle()
    {
        return $this->componentTitle;
    }

    public function setComponentPercentage($component_percentage)
    {
        $this->componentPercentage = $component_percentage;
        return $this;
    }

    public function getComponentPercentage()
    {
        return $this->componentPercentage;
    }

    public function setOldPercentage($old_percentage)
    {
        $this->oldPercentage = $old_percentage;
        return $this;
    }

    public function getOldPercentage()
    {
        return $this->oldPercentage;
    }

    public function setOldAmount($old_amount)
    {
        $this->oldAmount = $old_amount;
        return $this;
    }

    public function getOldAmount()
    {
        return $this->oldAmount;
    }

    public function setComponentAmount($component_amount)
    {
        $this->componentAmount = $component_amount;
        return $this;
    }

    public function getComponentAmount()
    {
        return $this->componentAmount;
    }

    public function setManagerMember($manager_member)
    {
        $this->managerMember = $manager_member;
        return $this;
    }

    public function getManagerMember()
    {
        return $this->managerMember;
    }
}