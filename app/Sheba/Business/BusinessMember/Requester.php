<?php namespace Sheba\Business\BusinessMember;

class Requester
{
    private $managerEmployee;
    private $role;
    private $joinDate;
    private $grade;
    private $employeeType;
    private $previousInstitution;
    private $status;
    private $businessId;
    private $memberId;

    /**
     * @param $manager_employee
     * @return $this
     */
    public function setManagerEmployee($manager_employee)
    {
        $this->managerEmployee = $manager_employee;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getManagerEmployee()
    {
        return $this->managerEmployee;
    }

    /**
     * @param $role
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param $join_date
     * @return $this
     */
    public function setJoinDate($join_date)
    {
        $this->joinDate = $join_date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getJoinDate()
    {
        return $this->joinDate;
    }

    /**
     * @param $grade
     * @return $this
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * @param $employee_type
     * @return $this
     */
    public function setEmployeeType($employee_type)
    {
        $this->employeeType = $employee_type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmployeeType()
    {
        return $this->employeeType;
    }

    /**
     * @param $previous_institution
     * @return $this
     */
    public function setPreviousInstitution($previous_institution)
    {
        $this->previousInstitution = $previous_institution;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPreviousInstitution()
    {
        return $this->previousInstitution;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $business_id
     * @return $this
     */
    public function setBusinessId($business_id)
    {
        $this->businessId = $business_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBusinessId()
    {
        return $this->businessId;
    }

    /**
     * @param $member_id
     * @return $this
     */
    public function setMemberId($member_id)
    {
        $this->memberId = $member_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMemberId()
    {
        return $this->memberId;
    }
}
