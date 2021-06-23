<?php namespace App\Sheba\Business\CoWorker;


use Sheba\Business\Role\Creator as RoleCreator;
use Sheba\Business\Role\Requester as RoleRequester;
use Sheba\Repositories\Interfaces\BusinessRoleRepositoryInterface;

class GetBusinessRole
{
    private $department;
    private $designation;
    private $roleRequester;
    private $roleCreator;
    private $businessRoleRepository;

    public function __construct($department, $designation)
    {
        $this->department = $department;
        $this->designation = $designation;
        $this->roleRequester = app(RoleRequester::class);
        $this->roleCreator = app(RoleCreator::class);
        $this->businessRoleRepository = app(BusinessRoleRepositoryInterface::class);
    }

    public function get()
    {
        $business_role = $this->businessRoleRepository
            ->where('name', $this->designation)
            ->where('business_department_id', $this->department)
            ->first();
        if ($business_role) return $business_role;
        return $this->businessRoleCreate();
    }

    private function businessRoleCreate()
    {
        $business_role_requester = $this->roleRequester
                                        ->setDepartment($this->department)
                                        ->setName($this->designation)
                                        ->setIsPublished(1);
        return $this->roleCreator->setRequester($business_role_requester)->create();
    }

}
