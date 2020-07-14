<?php namespace Sheba\Business\CoWorker\Requests;

use App\Models\BusinessMember;

class BasicRequest
{
    private $businessMember;
    private $proPic;
    private $firstName;
    private $lastName;
    private $email;
    private $department;
    /** @var $role | Designation */
    private $role;
    /** @var $managerEmployee | Manager */
    private $managerEmployee;

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
     * @param $pro_pic
     * @return $this
     */
    public function setProPic($pro_pic)
    {
        $this->proPic = $pro_pic;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProPic()
    {
        return $this->proPic;
    }

    /**
     * @param $first_name
     * @return $this
     */
    public function setFirstName($first_name)
    {
        $this->firstName = $first_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param $last_name
     * @return $this
     */
    public function setLastName($last_name)
    {
        $this->lastName = $last_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $department
     * @return $this
     */
    public function setDepartment($department)
    {
        $this->department = $department;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDepartment()
    {
        return $this->department;
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
     * @param $manager_employee
     * @return $this
     */
    public function setManagerEmployee($manager_employee)
    {
        $this->managerEmployee = $manager_employee ? $manager_employee : null;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getManagerEmployee()
    {
        return $this->managerEmployee;
    }
}
