<?php namespace App\Sheba\Business\CoWorker;

use App\Models\BusinessDepartment;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use Illuminate\Support\Collection;
use Sheba\Business\CoWorker\Statuses;
use Sheba\Repositories\Business\BusinessMemberRepository;

class ManagerSubordinateEmployeeList
{
    /*** @var BusinessMemberRepository $businessMemberRepository */
    private $businessMemberRepository;

    public function __construct()
    {
        $this->businessMemberRepository = app(BusinessMemberRepository::class);
    }

    /**
     * @param $business_member
     * @param null $department
     * @param null $is_employee_active
     * @return array
     */
    public function get($business_member, $department = null, $is_employee_active = null): array
    {
        $managers = $this->getManager($business_member->id);
        $managers_data = [];
        foreach ($managers as $manager) $managers_data[] = $this->formatSubordinateList($manager);
        $managers_data = $this->uniqueManagerData($managers_data);
        if ($department) return $this->filterEmployeeByDepartment($business_member, $managers_data, $is_employee_active);
        return $managers_data;
    }

    private function getManager($business_member_id): array
    {
        $manager_data = [];
        $managers = $this->getCoWorkersUnderSpecificManager($business_member_id);
        foreach ($managers as $manager)
        {
            $manager_data[] = $manager;
            foreach ($this->getManager($manager->id) as $next_manager) $manager_data[] = $next_manager;
        }
        return $manager_data;
    }

    /**
     * @param $business_member_id
     * @return Collection
     */
    private function getCoWorkersUnderSpecificManager($business_member_id)
    {
        return $this->businessMemberRepository->where('manager_id', $business_member_id)->get();
    }

    /**
     * @param $business_member
     * @param $managers_data
     * @param $is_employee_active
     * @return array
     */
    private function filterEmployeeByDepartment($business_member, $managers_data, $is_employee_active)
    {
        $filtered_unique_managers_data = $this->removeSpecificBusinessMemberIdFormUniqueManagersData($business_member, $managers_data);

        $data = [];
        foreach ($filtered_unique_managers_data as $manager) {
            if ($is_employee_active && $manager['is_active'] == 0) continue;
            $data[$manager['department']][] = $manager;
        }
        return $data;
    }

    /**
     * @param $managers_data
     * @return array
     */
    private function uniqueManagerData($managers_data)
    {
        $out = [];
        foreach ($managers_data as $row) {
            $out[$row['id']] = $row;
        }
        return array_values($out);
    }

    /**
     * @param $business_member
     * @param $unique_managers_data
     * @return array
     */
    public function removeSpecificBusinessMemberIdFormUniqueManagersData($business_member, $unique_managers_data)
    {
        return array_filter($unique_managers_data, function ($manager_data) use ($business_member) {
            return ($manager_data['id'] != $business_member->id);
        });
    }

    private function formatSubordinateList($business_member)
    {
        return $business_member->id;
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
