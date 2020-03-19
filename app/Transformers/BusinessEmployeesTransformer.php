<?php namespace App\Transformers;

use App\Models\Member;
use League\Fractal\TransformerAbstract;

class BusinessEmployeesTransformer extends TransformerAbstract
{
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
            $department_name = $this->isMemberRolePresent($member) ? $member->businessMember->role->businessDepartment->name : 'no_department';

            array_push($departments_name, $department_name);
            $employee_based_on_departments[$department_name][] = [
                'name' => $profile->name,
                'designation' => $this->isMemberRolePresent($member) ? $member->businessMember->role->name : 'N/S',
                'mobile' => $profile->mobile
            ];
        });

        return ['employees' => $employee_based_on_departments, 'departments' => array_values(array_unique($departments_name))];
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