<?php namespace Sheba\Business\Department;

use App\Models\Business;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;

class CreateRequest
{
    use HasErrorCodeAndMessage;

    /** @var DepartmentRepositoryInterface $departmentRepository */
    private $departmentRepository;
    private $business;
    private $name;
    private $abbreviation;

    /**
     * Creator constructor.
     * @param DepartmentRepositoryInterface $department_repository
     */
    public function __construct(DepartmentRepositoryInterface $department_repository)
    {
        $this->departmentRepository = $department_repository;
    }

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
        $this->existingNameCheck();
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
        $this->existingAbbreviationCheck();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }

    private function existingNameCheck()
    {
        $department_name = $this->departmentRepository->findByNameOrAbbreviation($this->name);
        if ($department_name) $this->setError(409, "This department name is already exist");
    }

    private function existingAbbreviationCheck()
    {
        $department_abbreviation = $this->departmentRepository->findByNameOrAbbreviation($this->name);
        if ($department_abbreviation) $this->setError(409, "This abbreviation is already exist");
    }

}