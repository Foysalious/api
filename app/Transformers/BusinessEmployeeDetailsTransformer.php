<?php namespace App\Transformers;

use App\Models\Member;
use League\Fractal\TransformerAbstract;

class BusinessEmployeeDetailsTransformer extends TransformerAbstract
{
    public function transform(Member $member)
    {
        $profile = $member->profile;
        $role = $member->businessMember->role;

        return [
            'name'          => $profile->name ? : null,
            'mobile'        => $profile->mobile,
            'email'         => $profile->email,
            'image'         => $profile->pro_pic,
            'designation'   => $role ? $role->name : null,
            'department'    => $role ? $role->businessDepartment->name : null
        ];
    }
}
