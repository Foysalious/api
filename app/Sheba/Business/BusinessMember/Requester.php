<?php namespace Sheba\Business\BusinessMember;

class Requester
{
    private $managerEmployee;
    private $role;

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
}