<?php namespace App\Transformers\Business;

use App\Sheba\Business\Appreciation\EmployeeAppreciations;
use League\Fractal\TransformerAbstract;
use App\Models\BusinessMember;

class AppreciationEmployeeTransformer extends TransformerAbstract
{
    const NO_DEPARTMENT_VALUE = 'OTHER';

    private $businessMember;

    public function __construct(BusinessMember $business_member)
    {
        $this->businessMember = $business_member;
    }

    /**
     * @param $business_members
     * @return array
     */
    public function transform($business_members)
    {
        $employee_based_on_departments = [];
        $departments_name = [];
        foreach ($business_members as $business_member) {

            if ($this->businessMember->id == $business_member->id) continue;

            $member = $business_member->member;
            $profile = $member->profile;
            $is_member_role_present = $this->isMemberRolePresent($business_member);
            $department_name = $is_member_role_present ? $business_member->role->businessDepartment->name : self::NO_DEPARTMENT_VALUE;
            $department_id = $is_member_role_present ? $business_member->role->businessDepartment->id : null;

            array_push($departments_name, $department_name);

            $employee_based_on_departments[$department_name][] = [
                'id' => $business_member->id,
                'name' => $profile->name,
                'pro_pic' => $profile->pro_pic,
                'mobile' => $business_member->mobile,
                'is_employee_new_joiner' => $business_member->isNewJoiner(),
                'department_id' => $department_id,
                'designation' => $is_member_role_present ? $business_member->role->name : 'N/S',
                'stickers' => (new EmployeeAppreciations())->getEmployeeStickers($business_member)
            ];
        }

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