<?php namespace Sheba\Business\Role;

class Requester
{
    private $department;
    private $name;
    private $isPublished;

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
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = trim($name);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $is_published
     * @return $this
     */
    public function setIsPublished($is_published)
    {
        $this->isPublished = $is_published;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }
}