<?php namespace App\Http\Controllers\Employee;

use App\Models\BusinessMember;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Sheba\Dal\LeaveType\Contract as LeaveTypesRepoInterface;
use App\Sheba\Leave\Creator as LeaveCreator;

class LeaveController extends Controller
{
    public function getLeaveTypes(Request $request, LeaveTypesRepoInterface $leave_types_repo)
    {
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $leave_types = $leave_types_repo->getAllLeaveTypesByBusiness($business_member->business);

        return api_response($request, null, 200, ['leave_types' => $leave_types]);
    }

    private function getBusinessMember(Request $request)
    {
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!isset($business_member['id'])) return null;
        return BusinessMember::find($business_member['id']);
    }

    public function store(Request $request, LeaveCreator $leave_creator)
    {
        try {
            $business_member = $this->getBusinessMember($request);
            if (!$business_member) return api_response($request, null, 404);

            $leave = $leave_creator->setTitle($request->title)->setBusinessMemberId($request->business_member_id)->setLeaveTypeId($request->leave_type_id)->create();
            return api_response($request, null,200,['leave' => $leave->id]);
        }
        catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
