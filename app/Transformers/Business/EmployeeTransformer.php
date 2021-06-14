<?php namespace App\Transformers\Business;

use App\Models\Member;
use League\Fractal\TransformerAbstract;

class EmployeeTransformer extends TransformerAbstract
{
    /**
     * @param Member $member
     * @return array
     */
    public function transform(Member $member)
    {
        $profile = $member->profile;
        $business_member = $member->businessMember;
        $role = $business_member ? $business_member->role : null;
        $department = $role ? $role->businessDepartment : null;

        return [
            'id' => $member->id,
            'name' => $profile->name,
            'mobile' => $business_member->mobile,
            'email' => $profile->email,
            'date_of_birth' => $profile->dob,
            'profile_picture' => $profile->pro_pic,
            'gender' => $profile->gender,
            'nid_no' => $profile->nid_no,
            'address' => $profile->address,
            'blood_group' => $profile->blood_group,
            'department_id' => $department ? $department->id : null,
            'department' => $department ? $department->name : null,
            'designation' => $role ? $role->name : null,
            'manager' => ($business_member && $business_member->manager) ? [
                'id' => $business_member->manager_id,
                'name' => $business_member->manager->member->profile->name
            ] : null
        ];
    }
}
