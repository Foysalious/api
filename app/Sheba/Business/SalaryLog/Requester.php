<?php namespace App\Sheba\Business\SalaryLog;


class Requester
{
    private $salary;
    private $businessMember;
    private $salaryRequest;

    public function setBusinessMember($business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function getBusinessMember()
    {
        return $this->businessMember;
    }

    public function setSalaryRequest($salary_request)
    {
        $this->salaryRequest = $salary_request;
        return $this;
    }

    public function getSalaryRequest()
    {
        return $this->salaryRequest;
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
}
