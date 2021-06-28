<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Profile;
use League\Fractal\TransformerAbstract;

class CoWorkerListTransformer extends TransformerAbstract
{
    public function transform(BusinessMember $business_member)
    {
        /** @var Member $member */
        $member = $business_member->member;
        /** @var Profile $profile */
        $profile = $member->profile;
        /** @var BusinessRole $role */
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
