<?php namespace Sheba\Repositories\Interfaces\Business;

use Sheba\Repositories\Interfaces\BaseRepositoryInterface;
use App\Models\Business;

interface DepartmentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByName($name, Business $business);

    public function findByAbbreviation($abbreviation, Business $business);

    public function getBusinessDepartmentByBusiness(Business $business);
}