<?php namespace App\Sheba\Business\SalaryLog\ComponentBreakdownLog;

use App\Models\BusinessMember;

class Requester
{
    private $businessMember;
    private $componentTitle;
    private $componentPercentage;
    private $componentAmount;
    private $salary;
    private $oldPercentage;
    private $managerMember;
    private $oldSalary;

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function setOldSalary($old_salary)
    {
        $this->oldSalary = $old_salary;
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

    public function getOldAmount()
    {
        return ($this->oldSalary * $this->oldPercentage) / 100;
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