<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use App\Models\Member;

class CoWorkerMinimumTransformer extends TransformerAbstract
{
    public function transform(Member $member)
    {
        $profile = $member->profile;
        $role = $member->businessMember->role;
        return [
            'id' => $member->businessMember->id,
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
