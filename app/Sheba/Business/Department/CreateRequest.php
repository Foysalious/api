<?php namespace Sheba\Business\Department;

use App\Models\Business;

class CreateRequest
{
    private $business;
    private $name;
    private $abbreviation;

    /**
     * @param Business $business
     * @return $this
     */
    public function setBusiness(Business $business)
    {
        $this->business = $business;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBusiness()
    {
        return $this->business;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setDepartmentName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDepartmentName()
    {
        return $this->name;
    }

    /**
     * @param $abbreviation
     * @return $this
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }

}