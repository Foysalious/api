<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use App\Models\Member;

class CoWorkerMinimumTransformer extends TransformerAbstract
{
    public function transform(Member $member)
    {
        $profile = $member->profile;
        $business_member = $member->businessMember;
        $role = $business_member ? $business_member->role : null;
        return [
            'id' => $business_member ? $business_member->id : null,
            'profile' => [
                'name' => $profile->name,
                'pro_pic' => $profile->pro_pic
            ],
            'department' => $role ? [
                'id' => $role->businessDepartment->id ,
                'name' => $role->businessDepartment->name
            ] : null,
            'designation' => $role ? $role->name : null
        ];
    }
}
