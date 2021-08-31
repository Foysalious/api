<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use League\Fractal\TransformerAbstract;

class CoWorkerMinimumTransformer extends TransformerAbstract
{
    public function transform(BusinessMember $business_member)
    {
        $profile = $business_member->profile();
        $role = $business_member ? $business_member->role : null;
        $business_department = $role ? $role->businessDepartment : null;

        return [
            'id' => $business_member ? $business_member->id : null,
            'profile' => [
                'name' => $profile->name,
                'pro_pic' => $profile->pro_pic
            ],
            'department' => $role ? [
                'id' => $business_department ? $business_department->id : null,
                'name' => $business_department ? $business_department->name : null
            ] : null,
            'designation' => $role ? $role->name : null
        ];
    }
}
