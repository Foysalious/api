<?php namespace Sheba\Repositories\Business;

use App\Models\Business;
use App\Models\BusinessDepartment;
use Sheba\Repositories\BaseRepository;
use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{
    public function __construct(BusinessDepartment $business_department)
    {
        parent::__construct();
        $this->setModel($business_department);
    }

    public function getBusinessDepartmentByBusiness(Business $business)
    {
        return $this->model->with('businessRoles')->published()->where('business_id', $business->id)
            ->select('id', 'business_id', 'name', 'abbreviation', 'created_at')
            /*->orderBy('id', 'DESC')*/
            ->get();
    }

    public function findByName($name)
    {
        return $this->model->where('name', $name)->first();
    }

    public function findByAbbreviation($abbreviation)
    {
        return $this->model->where('abbreviation', $abbreviation)->first();
    }
}