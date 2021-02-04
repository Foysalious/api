<?php namespace App\Sheba\Business\Salary;


use App\Models\Business;
use App\Models\BusinessMember;

class Requester
{
    /** @var Business */
    private $business;
    private $grossSalary;
    private $businessMember;

    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @param $gross_salary
     * @return $this
     */
    public function setGrossSalary($gross_salary)
    {
        $this->grossSalary = $gross_salary;
        return $this;
    }

    public function getGrossSalary()
    {
        return $this->grossSalary;
    }

    /**
     * @param $business_member
     * @return $this
     */
    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function getBusinessMember()
    {
        return $this->businessMember;
    }

}
