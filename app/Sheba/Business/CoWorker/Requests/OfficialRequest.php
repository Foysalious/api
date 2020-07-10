<?php namespace Sheba\Business\CoWorker\Requests;

use App\Models\BusinessMember;

class OfficialRequest
{
    private $businessMember;
    private $joinDate;
    private $grade;
    private $employeeType;
    private $previousInstitution;

    /**
     * @param $business_member
     * @return $this
     */
    public function setBusinessMember($business_member)
    {
        $this->businessMember = BusinessMember::findOrFail($business_member);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBusinessMember()
    {
        return $this->businessMember;
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
}