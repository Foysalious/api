<?php namespace App\Sheba\EmployeeTracking;


use App\Models\BusinessMember;

class Requester
{
    /** @var BusinessMember $businessMember*/
    private $businessMember;
    private $date;
    private $employee;
    private $title;
    private $description;

    public function setBusinessMember(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
        return $this;
    }

    public function getBusinessMember()
    {
        return $this->businessMember;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setEmployee($employee)
    {
        $this->employee = $employee;
        return $this;
    }

    public function getEmployee()
    {
        return $this->employee;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

}