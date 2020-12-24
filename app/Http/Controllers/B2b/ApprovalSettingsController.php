<?php namespace App\Http\Controllers\B2b;

use Sheba\Business\ApprovalSetting\ApprovalSettingRequester;
use Sheba\Business\ApprovalSetting\Creator;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\ProfileRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sheba\Dal\ApprovalSetting\ApprovalSettingRepository;
use Sheba\Dal\ApprovalSetting\Targets;
use Sheba\Dal\ApprovalSettingApprover\Types;
use Sheba\Dal\ApprovalSettingModule\Modules;

class ApprovalSettingsController extends Controller
{
    use ModificationFields;

    public function index(Request $request, ApprovalSettingRepository $approval_settings_repo, BusinessMemberRepositoryInterface $business_member_repo, ProfileRepository $profile_repo, DepartmentRepositoryInterface $department_repo)
    {
        $approval_settings = $approval_settings_repo->where('business_id', $request->business->id)->get();

        $data = [];
        foreach ($approval_settings as $approval_setting) {
            $modules = $approval_setting->modules;
            $approvars = $approval_setting->approvers;
            $module_data = [];
            $approvar_data = [];

            foreach ($modules as $module) {
                array_push($module_data, [
                    'id' => $module->id,
                    'approval_setting_id' => $module->approval_setting_id,
                    'name' => ucfirst(Modules::getModule($module->modules))
                ]);
            }
            foreach ($approvars as $approvar) {
                $business_member = $business_member_repo->where('id', $approvar->type_id)->get()->first();
                $member = $business_member ? $business_member->member : null;
                $profile = $member ? $profile_repo->where('id', $member->profile_id)->get()->first() : null;

                array_push($approvar_data, [
                    'id' => $approvar->id,
                    'approvar_type' => ucfirst(Types::getType($approvar->type)),
                    'approvar_type_id' => $approvar->type_id,
                    'approvar_name' => $profile ? $profile->name : null,
                    'approver_id' => $business_member ? $business_member->employee_id : null,
                    'approver_department' => $business_member ? $business_member->department : null,
                    'profile_pic' => $profile ? $profile->profile_pic : null
                ]);
            }
            $target_business_member = $approval_setting->target_type == Targets::EMPLOYEE ? $business_member_repo->where('id', $approval_setting->target_id)->get()->first() : null;
            $target_member = $target_business_member ? $target_business_member->member : null;
            $target_profile = $target_member ? $profile_repo->where('id', $target_member->profile_id)->get()->first() : null;
            $department = $approval_setting->target_type == Targets::DEPARTMENT ? $department_repo->where('id', $approval_setting->target_id)->select('id', 'name')->get()->first() : null;
            $target_type = [
                'id' => $approval_setting->target_id,
                'type' => ucfirst(Targets::getTargetType($approval_setting->target_type)),
                'employee' => $target_business_member ? [
                    'employee_id' => $target_business_member ? $target_business_member->employee_id : null,
                    'name' => $target_profile ? $target_profile->name : null,
                    'department' => $target_business_member ? $target_business_member->department() ? $target_business_member->department()->name : null : null,
                ] : null,
                'department' => $department ? ['id' => $department->id, 'name' => $department->name] : null
            ];
            array_push($data,
                [
                    'id' => $approval_setting->id,
                    'business_id' => $approval_setting->business_id,
                    'note' => $approval_setting->note,
                    'target_type' => $target_type,
                    'modules' => $module_data,
                    'approvars' => $approvar_data,
                    'approvar_count' => count($approvar_data),
                ]);
        }

        return api_response($request, null, 200, ['data' => $data]);
    }

    public function store(Request $request, ApprovalSettingRequester $approval_setting_requester, Creator $creator)
    {
        $this->validate($request, [
            'modules' => 'required|in:' . implode(',', Modules::get()),
            'note' => 'required|string',
            'target_type' => 'required|in:' . implode(',', Targets::get()),
            'approvers' => 'required',
        ]);
        $business = $request->business;
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
        $approval_setting_requester->setModules($request->modules)
            ->setTargetType($request->target_type)
            ->setTargetId($request->targetId)
            ->setNote($request->note)
            ->setApprovers($request->appovers);
        $creator->setApprovalSettingRequester($approval_setting_requester)->setBusiness($business)->create();
        return api_response($request, null, 200);

    }
}
