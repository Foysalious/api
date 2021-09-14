<?php namespace App\Transformers\Business;


use App\Models\BusinessMember;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class OfficialInfoTransformer extends TransformerAbstract
{

    public function transform(BusinessMember $business_member)
    {
        $member = $business_member->member;
        $profile = $member->profile;
        $role = $business_member ? $business_member->role : null;
        $department = $role ? $role->businessDepartment : null;
        $manager = $business_member->manager;
        $grade = $business_member->grade;
        $employee_id = $business_member->employee_id;
        $employee_type = $business_member->employee_type;

        return [
            'name' => $profile->name,
            'email' => $profile->email,
            'profile_picture' => $profile->pro_pic,
            'gender' => $profile->gender,
            'department_id' => $department ? $department->id : null,
            'department' => $department ? $department->name : null,
            'designation' => $role ? $role->name : null,
            'joining_date' => Carbon::parse($business_member->join_date)->format('d-m-Y'),
            'employee_id' => $employee_id,
            'employee_type' => $employee_type,
            'grade' => $grade,
            'manager' => ($business_member && $manager) ? [
                'id' => $business_member->manager_id,
                'name' => $manager->member->profile->name
            ] : null,
            'is_updatable' => ($manager && $employee_id && $employee_type && $grade) ? 0 : 1
        ];
    }
}
