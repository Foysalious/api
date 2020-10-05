<?php namespace App\Http\Controllers\B2b;

use App\Sheba\Business\BusinessBasicInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sheba\Business\LeaveType\Creator as LeaveTypeCreator;
use Sheba\Dal\LeaveType\Contract as LeaveTypesRepoInterface;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Business\LeaveType\OtherSettings\BasicInfo as OthersInfo;
use Sheba\Business\LeaveType\OtherSettings\Updater as OthersUpdater;

class LeaveSettingsController extends Controller
{
    use ModificationFields, BusinessBasicInformation;

    /**
     * @param Request $request
     * @param LeaveTypesRepoInterface $leave_types_repo
     * @param BusinessMemberRepositoryInterface $business_member_repo
     * @return JsonResponse
     */
    public function index(Request $request, LeaveTypesRepoInterface $leave_types_repo, BusinessMemberRepositoryInterface $business_member_repo)
    {
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 404);

        $business_member = $business_member_repo->find($business_member['id']);
        $leave_types = $leave_types_repo->getAllLeaveTypesWithTrashedByBusiness($business_member->business);

        return api_response($request, null, 200, ['leave_types' => $leave_types]);
    }

    /**
     * @param Request $request
     * @param LeaveTypeCreator $leave_type_creator
     * @return JsonResponse
     */
    public function store(Request $request, LeaveTypeCreator $leave_type_creator)
    {
        $this->validate($request, ['title' => 'required', 'total_days' => 'required', 'is_half_day_enable' => 'required']);
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 404);

        $leave_setting = $leave_type_creator->setBusiness($request->business)->setMember($business_member->member)
            ->setTitle($request->title)->setTotalDays($request->total_days)
            ->setIsLeaveHalfDayEnable($request->is_half_day_enable)
            ->create();

        return api_response($request, null, 200, ['leave_setting' => $leave_setting->id]);
    }

    /**
     * @param $business
     * @param LeaveType $leave_setting
     * @param Request $request
     * @param LeaveTypesRepoInterface $leave_types_repo
     * @return JsonResponse
     */
    public function update($business, $leave_setting, Request $request, LeaveTypesRepoInterface $leave_types_repo)
    {
        $this->validate($request, ['title' => 'required', 'total_days' => 'required', 'is_half_day_enable' => 'required']);
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 404);
        $this->setModifier($business_member->member);

        $leave_setting = $leave_types_repo->find($leave_setting);
        $data = [
            'title' => $request->title,
            'total_days' => $request->total_days,
            'is_half_day_enable' => $request->is_half_day_enable
        ];
        $leave_types_repo->update($leave_setting, $this->withUpdateModificationField($data));
        $leave_setting = $leave_types_repo->find($leave_setting->id);
        return api_response($request, null, 200, ['leave_setting' => $leave_setting]);
    }

    /**
     * @param $business
     * @param $leave_setting
     * @param Request $request
     * @param LeaveTypesRepoInterface $leave_types_repo
     * @return JsonResponse
     */
    public function delete($business, $leave_setting , Request $request, LeaveTypesRepoInterface $leave_types_repo)
    {
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 404);
        $this->setModifier($business_member->member);

        $leave_setting = $leave_types_repo->find($leave_setting);
        $this->withUpdateModificationField($leave_setting);
        $leave_setting->delete();

        return api_response($request, null, 200, ['msg' => "Deleted Successfully"]);
    }

    /**
     * @param Request $request
     * @param OthersInfo $info
     * @return JsonResponse
     */
    public function othersInfo(Request $request, OthersInfo $info)
    {
        $others_info = $info->setBusiness($request->business)->getInfo();
        return api_response($request, null, 200, ['others_info' => $others_info]);
    }

    /**
     * @param Request $request
     * @param OthersUpdater $updater
     * @return JsonResponse
     */
    public function othersUpdate(Request $request, OthersUpdater $updater)
    {
        $this->validate($request, ['sandwich_leave' => 'required', 'fiscal_year' => 'required']);
        $business_member = $request->business_member;
        $this->setModifier($business_member->member);

        $updater->setBusiness($request->business)
            ->setMember($business_member->member)
            ->setSandwichLeave($request->sandwich_leave)
            ->setFiscalYear($request->fiscal_year)
            ->update();

        return api_response($request, null, 200);
    }
}
