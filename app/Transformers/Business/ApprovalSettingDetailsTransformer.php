<?php namespace App\Transformers\Business;


use App\Models\Business;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\ApprovalSetting\Targets;
use Sheba\Dal\ApprovalSettingApprover\Types;
use Sheba\Dal\ApprovalSettingModule\Modules;
use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\ProfileRepository;

class ApprovalSettingDetailsTransformer extends TransformerAbstract
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
     * ApprovalSettingTransformer constructor.
     * @param DepartmentRepositoryInterface $department_repo
     * @param BusinessMemberRepositoryInterface $business_member_repo
     * @param ProfileRepository $profile_repo
     */
    public function __construct(DepartmentRepositoryInterface $department_repo, BusinessMemberRepositoryInterface $business_member_repo, ProfileRepository $profile_repo)
    {
        $this->departmentRepo = $department_repo;
        $this->businessMemberRepo = $business_member_repo;
        $this->profileRepo = $profile_repo;

    }

    /**
     * @param Business $business
     * @return mixed
     */
    public function transform($approval_setting)
    {
        $module_data = [];
        $approvar_data = [];

        $modules = $approval_setting->modules;
        $approvars = $approval_setting->approvers;

        foreach ($modules as $module) {
            array_push($module_data, [
                'id' => $module->id, 'approval_setting_id' => $module->approval_setting_id, 'name' => ucfirst(Modules::getModule($module->modules))
            ]);
        }

        foreach ($approvars as $approvar) {
            $business_member = $this->businessMemberRepo->where('id', $approvar->type_id)->get()->first();
            $member = $business_member ? $business_member->member : null;
            $profile = $member ? $this->profileRepo->where('id', $member->profile_id)->get()->first() : null;

            array_push($approvar_data, [
                'id' => $approvar->id, 'approvar_type' => ucfirst(Types::getType($approvar->type)), 'approvar_type_id' => $approvar->type_id, 'approvar_name' => $profile ? $profile->name : null, 'approver_id' => $business_member ? $business_member->employee_id : null, 'approver_department' => $business_member ? $business_member->department : null, 'profile_pic' => $profile ? $profile->profile_pic : null
            ]);
        }
        $target_business_member = $approval_setting->target_type == Targets::EMPLOYEE ? $this->businessMemberRepo->where('id', $approval_setting->target_id)->get()->first() : null;
        $target_member = $target_business_member ? $target_business_member->member : null;
        $target_profile = $target_member ? $this->profileRepo->where('id', $target_member->profile_id)->get()->first() : null;
        $department = $approval_setting->target_type == Targets::DEPARTMENT ? $this->departmentRepo->where('id', $approval_setting->target_id)->select('id', 'name')->get()->first() : null;
        $target_type = [
            'id' => $approval_setting->target_id, 'type' => ucfirst(Targets::getTargetType($approval_setting->target_type)), 'employee' => $target_business_member ? [
                'employee_id' => $target_business_member ? $target_business_member->employee_id : null, 'name' => $target_profile ? $target_profile->name : null, 'department' => $target_business_member ? $target_business_member->department() ? $target_business_member->department()->name : null : null,
            ] : null, 'department' => $department ? ['id' => $department->id, 'name' => $department->name] : null
        ];
        return [
            'id' => $approval_setting->id, 'business_id' => $approval_setting->business_id, 'note' => $approval_setting->note, 'target_type' => $target_type, 'modules' => $module_data, 'approvars' => $approvar_data,
        ];
    }
}
