<?php namespace App\Transformers;

use App\Models\Member;
use League\Fractal\TransformerAbstract;

class BusinessEmployeesTransformer extends TransformerAbstract
{
    CONST NO_DEPARTMENT_VALUE = 'OTHER';

    /**
     * @param $members
     * @return array
     */
    public function transform($members)
    {
        $employee_based_on_departments = [];
        $departments_name = [];
        $members->each(function ($member) use (&$employee_based_on_departments, &$departments_name) {
            $profile = $member->profile;
            $department_name = $this->isMemberRolePresent($member) ? $member->businessMember->role->businessDepartment->name : self::NO_DEPARTMENT_VALUE;

            array_push($departments_name, $department_name);
            $employee_based_on_departments[$department_name][] = [
                'id' => $member->businessMember->id,
                'name' => $profile->name,
                'designation' => $this->isMemberRolePresent($member) ? $member->businessMember->role->name : 'N/S',
                'mobile' => $profile->mobile
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

        return ['employees' => $employee_based_on_departments, 'departments' => $departments];
    }

    /**
     * @param Member $member
     * @return bool
     */
    private function isMemberRolePresent(Member $member)
    {
        return $member->businessMember->role ? true : false;
    }
}
