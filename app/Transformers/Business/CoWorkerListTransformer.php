<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use App\Models\Member;

class CoWorkerListTransformer extends TransformerAbstract
{
    public function transform(Member $member)
    {
        $profile = $member->profile;
        $business_member = $member->businessMember;
        $role = $business_member->role;
        return [
            'id' => $member->id,
            'business_member_id' => $business_member->id,
            'profile' => [
                'id' => $profile->id,
                'name' => $profile->name,
                'pro_pic' => $profile->pro_pic,
                'mobile' => $profile->mobile,
                'email' => $profile->email,
            ],
            'status' => $member->businessMember->status,
            'department_id' => $role ? $role->businessDepartment->id : null,
            'department' => $role ? $role->businessDepartment->name : null,
            'designation' => $role ? $role->name : null
        ];
    }
}