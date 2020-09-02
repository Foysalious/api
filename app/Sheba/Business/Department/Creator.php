<?php namespace Sheba\Business\Department;

use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Sheba\ModificationFields;

class Creator
{
    use ModificationFields;

    /** @var DepartmentRepositoryInterface $departmentRepository */
    private $departmentRepository;
    /** @var CreateRequest $departmentCreateRequest */
    private $departmentCreateRequest;

    /**
     * Creator constructor.
     * @param DepartmentRepositoryInterface $department_repository
     */
    public function __construct(DepartmentRepositoryInterface $department_repository)
    {
        $this->departmentRepository = $department_repository;
    }

    /**
     * @param CreateRequest $create_request
     * @return $this
     */
    public function setDepartmentCreateRequest(CreateRequest $create_request)
    {
        $this->departmentCreateRequest = $create_request;
        return $this;
    }

    public function create()
    {
        DB::transaction(function () {
            $this->departmentRepository->create($this->withCreateModificationField($this->formatDepartmentSpecificData()));
        });
    }

    private function formatDepartmentSpecificData()
    {
        return [
            'business_id' => $this->departmentCreateRequest->getBusiness()->id,
            'name' => strtoupper($this->departmentCreateRequest->getDepartmentName()),
            #'abbreviation' => strtoupper($this->departmentCreateRequest->getAbbreviation()),
            'is_published' => 1
        ];
    }

}