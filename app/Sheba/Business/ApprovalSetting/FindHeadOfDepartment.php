<?php namespace Sheba\Business\ApprovalSetting;


class FindHeadOfDepartment
{
    /**
     * @var array
     */
    private $managers;
    /**
     * @var array
     */
    private $departments;
    /**
     * @var null
     */
    private $initialDepartment;
    /**
     * @var null
     */
    private $headOfDepartment;

    public function __construct()
    {
        $this->managers = [];
        $this->departments = [];
        $this->initialDepartment = null;
        $this->headOfDepartment = null;
    }

    public function getManager($business_member)
    {
        $manager = $business_member->manager()->first();

        if ($manager) {
            if (in_array($manager->id, $this->managers)) {
                return;
            }
            $department = $manager->department();
            if (!$this->initialDepartment) $this->initialDepartment = $department;
            if ($this->initialDepartment->id == $department->id) {
                $this->headOfDepartment = $manager;
            }
            array_push($this->managers, $manager->id);
            $this->getManager($manager);
        }
        return;
    }
}