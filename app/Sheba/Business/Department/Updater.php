<?php namespace Sheba\Business\Department;

use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Models\BusinessDepartment;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;

    /** @var DepartmentRepositoryInterface $departmentRepository */
    private $departmentRepository;
    /** @var UpdateRequest $departmentUpdateRequest */
    private $departmentUpdateRequest;
    private $department;
    private $formatDepartmentData;

    /**
     * Updater constructor.
     * @param DepartmentRepositoryInterface $department_repository
     */
    public function __construct(DepartmentRepositoryInterface $department_repository)
    {
        $this->departmentRepository = $department_repository;
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

    /**
     * @param BusinessDepartment $department
     * @return $this
     */
    public function setDepartment(BusinessDepartment $department)
    {
        $this->department = $department;
        return $this;
    }

    public function update()
    {
        $this->formatDepartmentSpecificData();
        DB::transaction(function () {
            $this->departmentRepository->update($this->department, $this->withCreateModificationField($this->formatDepartmentData));
        });
    }

    private function formatDepartmentSpecificData()
    {
        if ($this->departmentUpdateRequest->getDepartmentName()) $this->formatDepartmentData['name'] = strtoupper($this->departmentUpdateRequest->getDepartmentName());
        if ($this->departmentUpdateRequest->getAbbreviation()) $this->formatDepartmentData['abbreviation'] = strtoupper($this->departmentUpdateRequest->getAbbreviation());
    }
}