<?php namespace App\Transformers\Business;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use League\Fractal\TransformerAbstract;

class BusinessDepartmentListTransformer extends TransformerAbstract
{
    public function transform(BusinessDepartment $business_department)
    {
        $business_roles_id = $business_department->businessRoles->pluck('id')->toArray();
        $total_employee = BusinessMember::whereIn('Business_role_id', $business_roles_id)->count();

        return [
            'id' => $business_department->id,
            'name' => strtoupper($business_department->name),
            'abbreviation' => strtoupper($business_department->abbreviation),
            'total_employee' => $total_employee,
            'created_at' => $business_department->created_at->format('d/m/y')
        ];
    }
}
