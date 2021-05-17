<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use App\Models\Member;

class CoWorkerListTransformer extends TransformerAbstract
{
    private $isInactiveFilterApplied;

    public function __construct($is_inactive_filter_applied)
    {
        $this->isInactiveFilterApplied = $is_inactive_filter_applied;
    }

    public function transform(Member $member)
    {
        $profile = $member->profile;
        if ($this->isInactiveFilterApplied)
            $business_member = $member->businessMemberGenerated;
        else
            $business_member = $member->businessMember;

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
