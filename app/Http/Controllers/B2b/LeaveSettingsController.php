<?php namespace App\Http\Controllers\B2b;

use App\Sheba\Business\BusinessBasicInformation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Sheba\Dal\LeaveType\Contract as LeaveTypesRepoInterface;
use Sheba\Dal\LeaveType\Model as LeaveType;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

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
        $leaves = $leave_types_repo->getAllLeaveTypesByBusiness($business_member->business);
        $leave_types = $leaves->map(function ($leave) {
            return collect($leave->toArray())
                ->only(['id', 'title', 'total_days'])
                ->all();
        });
        return api_response($request, null, 200, ['leave_types' => $leave_types]);
    }

    /**
     * @param Request $request
     * @param LeaveTypesRepoInterface $leave_types_repo
     * @return JsonResponse
     */
    public function store(Request $request, LeaveTypesRepoInterface $leave_types_repo)
    {
        $this->validate($request, ['title' => 'required', 'total_days' => 'required']);
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 404);

        $this->setModifier($business_member->member);
        $data = [
            'business_id' => $business_member['business_id'],
            'title' => $request->title,
            'total_days' => $request->total_days
        ];
        $leave_setting = $leave_types_repo->create($this->withCreateModificationField($data));
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
        $this->validate($request, ['title' => 'required', 'total_days' => 'required']);
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 404);
        $this->setModifier($business_member->member);

        $leave_setting = $leave_types_repo->find($leave_setting);
        $data = [
            'title' => $request->title,
            'total_days' => $request->total_days
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
}
