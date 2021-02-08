<?php namespace App\Sheba\Business\SalaryLog;


class Requester
{
    private $salary;
    private $businessMember;
    private $grossSalary;
    private $oldSalary;
    private $profile;
    private $managerMember;

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function getBusinessMember()
    {
        return $this->businessMember;
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

    public function setGrossSalary($gross_salary)
    {
        $this->grossSalary = $gross_salary;
        return $this;
    }

    public function getGrossSalary()
    {
        return $this->grossSalary;
    }

    public function setOldSalary($old_salary)
    {
        $this->oldSalary = $old_salary;
        return $this;
    }

    public function getOldSalary()
    {
        return $this->oldSalary;
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
