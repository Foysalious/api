<?php namespace App\Transformers\Business;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use League\Fractal\TransformerAbstract;
use Sheba\Business\CoWorker\Statuses;

class CoWorkerManagerListTransformer extends TransformerAbstract
{
    /**
     * @param BusinessMember $business_member
     * @return array
     */
    public function transform(BusinessMember $business_member)
    {
        /** @var Member $member */
        $member = $business_member->member;
        /** @var Profile $profile */
        $profile = $member->profile;
        /** @var BusinessRole $role */
        $role = $business_member->role;
        /** @var BusinessDepartment $department */
        $department = $role ? $role->businessDepartment : null;

        return [
            'id' => $business_member->id,
            'name' => $profile->name,
            'pro_pic' => $profile->pro_pic,
            'phone' => $business_member->mobile,
            'designation' => $role ? $role->name : null,
            'department_id' => $department ? $department->id : null,
            'department' => $department ? $department->name : null,
            'manager_id' => $business_member->manager_id,
            'is_active' => $business_member->status === Statuses::ACTIVE ? 1 : 0
        ];
    }

}