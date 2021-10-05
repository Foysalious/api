<?php namespace App\Transformers\Business;

use App\Models\BusinessMember;
use League\Fractal\TransformerAbstract;

class AppreciationEmployeeTransformer extends TransformerAbstract
{
    CONST NO_DEPARTMENT_VALUE = 'OTHER';

    /**
     * @param $business_members
     * @return array
     */
    public function transform($business_members)
    {
        $employee_based_on_departments = [];
        $departments_name = [];
        $business_members->each(function ($business_member) use (&$employee_based_on_departments, &$departments_name) {
            $member = $business_member->member;
            $profile = $member->profile;
            $is_member_role_present = $this->isMemberRolePresent($business_member);
            $department_name = $is_member_role_present ? $business_member->role->businessDepartment->name : self::NO_DEPARTMENT_VALUE;

            array_push($departments_name, $department_name);
            $employee_based_on_departments[$department_name][] = [
                'id' => $business_member->id,
                'name' => $profile->name,
                'pro_pic' => $profile->pro_pic,
                'mobile' => $business_member->mobile,
                'designation' => $is_member_role_present ? $business_member->role->name : 'N/S'
            ];
        });

        $departments = array_values(array_unique($departments_name));

        if (in_array(self::NO_DEPARTMENT_VALUE, $departments_name)) {
            $v = $employee_based_on_departments[self::NO_DEPARTMENT_VALUE];
            unset($employee_based_on_departments[self::NO_DEPARTMENT_VALUE]);
            $employee_based_on_departments[self::NO_DEPARTMENT_VALUE] = $v;

            unset($departments[array_search(self::NO_DEPARTMENT_VALUE, $departments)]);
            array_push($departments, self::NO_DEPARTMENT_VALUE);
        }

        return ['employees' => $employee_based_on_departments, 'departments' => array_values($departments)];
    }

    /**
     * @param BusinessMember $business_member
     * @return bool
     */
    private function isMemberRolePresent(BusinessMember $business_member)
    {
        return $business_member->role ? true : false;
    }
}