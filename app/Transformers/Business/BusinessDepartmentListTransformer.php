<?php namespace App\Transformers\Business;

use App\Models\BusinessDepartment;
use League\Fractal\TransformerAbstract;

class BusinessDepartmentListTransformer extends TransformerAbstract
{
    public function transform(BusinessDepartment $business_department)
    {
       # $business_roles = $business_department->businessRoles()->count();
        return [
            'id' => $business_department->id,
            'name' => strtoupper($business_department->name),
            'abbreviation' => strtoupper($business_department->name),
            'total_employee' => 10,
            'created_at' => $business_department->created_at->format('d/m/y')
        ];
    }
}