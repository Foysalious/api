<?php namespace App\Transformers;

use App\Models\Member;
use League\Fractal\TransformerAbstract;

class BusinessEmployeeDetailsTransformer extends TransformerAbstract
{
    /**
     * @param $members
     * @return array
     */
    public function transform($business_member)
    {
        $profile = $business_member->profile;
        $business_member1 = $business_member->businessMember;
        $role = $business_member1 ? $business_member1->role : null;
        $department = $role ? $role->businessDepartment : null;

        return [
            'name' => $profile->name,
            'designation' => $role ? $role->name : null,
            'mobile' => $profile->mobile,
            'email' => $profile->email,
            'profile_picture' => $profile->pro_pic,
            'department' => $department ? $department->name : null,
        ];
    }

}