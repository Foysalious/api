<?php namespace Sheba\Business\Department;

use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;

class Updater
{
    /** @var UpdateRequest $departmentUpdateRequest */
    private $departmentUpdateRequest;

    /** @var DepartmentRepositoryInterface $departmentRepository */
    private $departmentRepository;

    public function __construct(DepartmentRepositoryInterface $department_repository)
    {
        $this->departmentRepository =$department_repository;
    }

    /**
     * @param UpdateRequest $update_request
     * @return $this
     */
    public function setDepartmentUpdateRequest(UpdateRequest $update_request)
    {
        $this->departmentUpdateRequest = $update_request;
        return $this;
    }
    public function update()
    {
        DB::transaction(function () {

        });
    }
    private function formatDepartmentSpecificData()
    {
        return [
            'business_id' => $business->id,
            'name' => $request->name,
            'abbreviation' => $request->abbreviation,
            'is_published' => 1
        ];
    }
}