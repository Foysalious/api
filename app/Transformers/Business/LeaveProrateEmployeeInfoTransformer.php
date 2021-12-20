<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use Sheba\Repositories\Interfaces\MemberRepositoryInterface;

class LeaveProrateEmployeeInfoTransformer extends TransformerAbstract
{
    /*** @var MemberRepositoryInterface $memberRepository*/
    private $memberRepository;

    public function __construct()
    {
        $this->memberRepository = app(MemberRepositoryInterface::class);
    }

    public function transform($prorate)
    {
        $business_member = $prorate->businessMember;
        $business_member_join_date = $business_member->join_date;
        $member = $business_member->member;
        $profile = $member->profile;
        $department = $business_member->department();
        $updated_by = $prorate->updated_by_name ? $this->memberRepository->find($prorate->updated_by) : null;
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
            'business_member_joined_date' => $business_member_join_date ? $business_member_join_date->format('F Y') : null,
            'leave_type' => $prorate->leaveType->title,
            'total_days' => $prorate->total_days,
            'is_auto_prorated' => $prorate->is_auto_prorated,
            'created_at' => $prorate->created_at->format('Y-m-d H:i:s'),
            'updated_by' => $updated_by ? [
                'employee_id' => $updated_by->businessMember->employee_id,
                'profile_pic' => $updated_by->businessMember->profile()->pro_pic,
                'employee_name' => $updated_by->businessMember->profile()->name,
            ] : null
        ];
    }
}