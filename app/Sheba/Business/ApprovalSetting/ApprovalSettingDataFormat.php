<?php namespace Sheba\Business\ApprovalSetting;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use Illuminate\Foundation\Application;
use Sheba\Dal\ApprovalSetting\Targets;
use Sheba\Dal\ApprovalSettingApprover\Types;
use Sheba\Dal\ApprovalSettingModule\Modules;
use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class ApprovalSettingDataFormat
{

    /**
     * @var Application|mixed
     */
    private $departmentRepo;
    /**
     * @var Application|mixed
     */
    private $businessMemberRepo;

    public function __construct()
    {
        $this->departmentRepo = app(DepartmentRepositoryInterface::class);
        $this->businessMemberRepo = app(BusinessMemberRepositoryInterface::class);
    }
    /**
     * @param $modules
     * @return array
     */
    public function getModules($modules)
    {
        $module_data = [];
        foreach ($modules as $module) {
            array_push($module_data, [
                'id' => $module->id,
                'approval_setting_id' => $module->approval_setting_id,
                'name' => ucfirst(Modules::getModule($module->modules))
            ]);
        }
        return $module_data;
    }

    /**
     * @param $approvars
     * @return array
     */
    public function getApprovers($approvars)
    {
        $approvar_data = [];
        foreach ($approvars as $approvar) {
            /** @var BusinessMember $business_member */
            $business_member = $this->businessMemberRepo->find($approvar->type_id);
            /** @var Member $member */
            $member = $business_member ? $business_member->member : null;
            /** @var Profile $profile */
            $profile = $member ? $member->profile : null;

            array_push($approvar_data, [
                'id' => $approvar->id,
                'type' => ucfirst(Types::getType($approvar->type)),
                'type_id' => $approvar->type_id,
                'name' => $profile ? $profile->name : null,
                'employee_id' => $business_member ? $business_member->employee_id : null,
                'department' => $business_member ? $business_member->department() ? $business_member->department()->name : null : null,
                'profile_pic' => $profile ? $profile->pro_pic : null
            ]);
        }
        return $approvar_data;
    }

    /**
     * @param $approval_setting
     * @return array
     */
    public function getTargetTypes($approval_setting)
    {
        return [
            'id' => $approval_setting->target_id,
            'type' => ucfirst(Targets::getTargetType($approval_setting->target_type)),
            'employee' => $this->getTargetEmployee($approval_setting),
            'department' => $this->getTargetDepartment($approval_setting)
        ];
    }

    /**
     * @param $approval_setting
     * @return array|null
     */
    public function getTargetEmployee($approval_setting)
    {
        /** @var BusinessMember $business_member */
        $business_member = $approval_setting->target_type == Targets::EMPLOYEE ? $this->businessMemberRepo->find($approval_setting->target_id) : null;
        /** @var Member $member */
        $member = $business_member ? $business_member->member : null;
        /** @var Profile $profile */
        $profile = $member ? $member->profile : null;

        return $business_member ? [
            'employee_id' => $business_member ? $business_member->employee_id : null,
            'name' => $profile ? $profile->name : null,
            'department' => $business_member ? $business_member->department() ? $business_member->department()->name : null : null,
        ] : null;
    }

    /**
     * @param $approval_setting
     * @return array|null
     */
    public function getTargetDepartment($approval_setting)
    {
        /** @var BusinessDepartment $business_department */
        $business_department = $approval_setting->target_type == Targets::DEPARTMENT ? $this->departmentRepo->find($approval_setting->target_id) : null;

        return $business_department ? [
            'id' => $business_department->id,
            'name' => $business_department->name
        ] : null;
    }
}