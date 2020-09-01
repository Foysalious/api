<?php namespace Sheba\Business\Department;

use App\Models\Business;
use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;

class Creator
{
    /** @var CreateRequest $departmentCreateRequest */
    private $departmentCreateRequest;
    private $member;
    /** @var DepartmentRepositoryInterface $departmentRepository */
    private $departmentRepository;

    public function __construct(DepartmentRepositoryInterface $department_repository)
    {
        $this->departmentRepository =$department_repository;
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

    public function hasError()
    {

    }

    public function create()
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