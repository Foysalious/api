<?php namespace Sheba\Repositories\Interfaces\Business;

use Sheba\Repositories\Interfaces\BaseRepositoryInterface;
use App\Models\Business;

interface DepartmentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByNameOrAbbreviation($identity);

    public function getBusinessDepartmentByBusiness(Business $business);
}