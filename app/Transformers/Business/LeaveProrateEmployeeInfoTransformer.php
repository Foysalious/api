<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;

class LeaveProrateEmployeeInfoTransformer extends TransformerAbstract
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
            'profile_id' => $profile->id,
            'business_member_id' => $business_member->id,
            'leave_type_id' => $prorate->leaveType->id,
            'employee_department_id' => $department ? $department->id : null,
            'employee_name' => $profile->name,
            'employee_pro_pic' => $profile->pro_pic,
            'employee_department_name' => $department ? $department->name : null,
            'business_member_joined_date' => $business_member->join_date->format('F Y'),
            'leave_type' => $prorate->leaveType->title,
            'total_days' => $prorate->total_days,
            'updated_by' => $prorate->updated_by_name,
            'is_auto_prorated' => $prorate->is_auto_prorated,
            'created_at' => $prorate->created_at->format('Y-m-d H:i:s')
        ];
    }
}