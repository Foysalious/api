<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class BusinessMemberLeaveProrateTransformer extends TransformerAbstract
{
    public function transform($prorate)
    {
        $business_member = $prorate->businessMember;
        $member = $business_member->member;
        $profile = $member->profile;
        $department = $business_member->department();
        return [
            'id' => $prorate->id,
            'employee_id' => $business_member->employee_id,
            'business_member_id' => $business_member->id,
            'leave_type_id' => $prorate->leaveType->id,
            'leave_type' => $prorate->leaveType->title,
            'total_days' => $prorate->total_days,
            'note' => $prorate->note,
            'is_auto_prorated' => $prorate->is_auto_prorated,
            'created_at' => $prorate->created_at->format('m/d/y'),
            'profile' => [
                'id' => $profile->id,
                'name' => $profile->name,
                'pro_pic' => $profile->pro_pic,
                'department_id' => $department ? $department->id : null,
                'department_name' => $department ? $department->name : null
            ]
        ];
    }

}