<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use Sheba\Business\ApprovalSetting\MakeDefaultApprovalSetting;
use Sheba\Dal\ApprovalSetting\Targets;
use Sheba\Dal\ApprovalSettingApprover\Types;
use Sheba\Dal\ApprovalSettingModule\Modules;
use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\ProfileRepository;

class ApprovalSettingListTransformer extends TransformerAbstract
{
    /**
     * @var DepartmentRepositoryInterface
     */
    private $departmentRepo;
    /**
     * @var BusinessMemberRepositoryInterface
     */
    private $businessMemberRepo;
    /**
     * @var ProfileRepository
     */
    private $profileRepo;
    /**
     * @var MakeDefaultApprovalSetting
     */
    private $defaultApprovalSetting;

    /**
     * ApprovalSettingListTransformer constructor.
     */
    public function __construct()
    {
        $this->departmentRepo = app(DepartmentRepositoryInterface::class);
        $this->businessMemberRepo = app(BusinessMemberRepositoryInterface::class);
        $this->profileRepo = app(ProfileRepository::class);
        $this->defaultApprovalSetting = app(MakeDefaultApprovalSetting::class);
    }

    /**
     * @param $approval_setting
     * @return mixed
     */
    public function transform($approval_setting)
    {
        $modules = $approval_setting->modules;
        $approvars = $approval_setting->approvers;

        $module_data = $this->getModules($modules);
        $approvar_data = $this->getApprovers($approvars);

        $target_business_member = $approval_setting->target_type == Targets::EMPLOYEE ? $this->businessMemberRepo->where('id', $approval_setting->target_id)->get()->first() : null;
        $target_member = $target_business_member ? $target_business_member->member : null;
        $target_profile = $target_member ? $this->profileRepo->where('id', $target_member->profile_id)->get()->first() : null;

        $department = $approval_setting->target_type == Targets::DEPARTMENT ?
            $this->departmentRepo->where('id', $approval_setting->target_id)->select('id', 'name')->get()->first()
            : null;

        $target_type = [
            'id' => $approval_setting->target_id,
            'type' => ucfirst(Targets::getTargetType($approval_setting->target_type)),
            'employee' => $target_business_member ? [
                'employee_id' => $target_business_member ? $target_business_member->employee_id : null,
                'name' => $target_profile ? $target_profile->name : null,
                'department' => $target_business_member ? $target_business_member->department() ? $target_business_member->department()->name : null : null,
            ] : null,
            'department' => $department ? [
                'id' => $department->id,
                'name' => $department->name
            ] : null
        ];
        return $this->defaultApprovalSetting->getApprovalSettings();
        return [
            'id' => $approval_setting->id,
            'business_id' => $approval_setting->business_id,
            'is_default' => 0,
            'note' => $approval_setting->note,
            'target_type' => $target_type,
            'modules' => $module_data,
            'approvers' => $approvar_data,
            'approver_count' => count($approvar_data),
        ];
    }

    /**
     * @param $modules
     * @return array
     */
    private function getModules($modules)
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
    private function getApprovers($approvars)
    {
        $approvar_data = [];
        foreach ($approvars as $approvar) {
            $business_member = $this->businessMemberRepo->where('id', $approvar->type_id)->get()->first();
            $member = $business_member ? $business_member->member : null;
            $profile = $member ? $this->profileRepo->where('id', $member->profile_id)->get()->first() : null;

            array_push($approvar_data, [
                'id' => $approvar->id,
                'type' => ucfirst(Types::getType($approvar->type)),
                'type_id' => $approvar->type_id,
                'name' => $profile ? $profile->name : null,
                'employee_id' => $business_member ? $business_member->employee_id : null,
                'department' => $business_member ? $business_member->department : null,
                'profile_pic' => $profile ? $profile->profile_pic : null
            ]);
        }
        return $approvar_data;
    }
}
