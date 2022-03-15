<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use League\Fractal\TransformerAbstract;

class CoWorkerListTransformer extends TransformerAbstract
{
    private $isPayrollEnable;

    public function __construct($is_payroll_enable)
    {
        $this->isPayrollEnable = $is_payroll_enable;
    }

    public function transform(BusinessMember $business_member)
    {
        /** @var Member $member */
        $member = $business_member->member;
        /** @var Profile $profile */
        $profile = $member->profile;
        /** @var BusinessRole $role */
        $role = $business_member->role;
        $show_alert = $business_member->salary ? 0 : 1;
        //dd($business_member->salary);
        return [
            'id' => $member->id,
            'employee_id' => $business_member->employee_id,
            'business_member_id' => $business_member->id,
            'is_super' => $business_member->is_super,
            'profile' => [
                'id' => $profile->id,
                'name' => $profile->name ?: null,
                'pro_pic' => $profile->pro_pic,
                'mobile' => $business_member->mobile,
                'email' => $profile->email,
            ],
            'status' => $business_member->status,
            'department_id' => $role ? $role->businessDepartment->id : null,
            'department' => $role ? $role->businessDepartment->name : null,
            'designation' => $role ? $role->name : null,
            'show_alert' => $this->isPayrollEnable ? $show_alert : 0
        ];
    }
}
