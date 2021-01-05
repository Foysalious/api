<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use League\Fractal\TransformerAbstract;

class CoWorkerListTransformer extends TransformerAbstract
{
    /**
     * @param BusinessMember $business_member
     * @return array
     */
    public function transform(BusinessMember $business_member)
    {
        $member = $business_member->member;
        $profile = $member->profile;

        $role = $business_member->role;
        return [
            'id' => $member->id,
            'employee_id' => $business_member->employee_id,
            'business_member_id' => $business_member->id,
            'is_super' => $business_member->is_super,
            'profile' => [
                'id' => $profile->id,
                'name' => $profile->name,
                'pro_pic' => $profile->pro_pic,
                'mobile' => $business_member->mobile,
                'email' => $profile->email,
            ],
            'status' => $business_member->status,
            'department_id' => $role ? $role->businessDepartment->id : null,
            'department' => $role ? $role->businessDepartment->name : null,
            'designation' => $role ? $role->name : null
        ];
    }
}
