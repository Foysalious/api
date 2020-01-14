<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use Illuminate\Http\Request;
use Sheba\Business\AttendanceActionLog\AttendanceAction;
use Sheba\Dal\Attendance\EloquentImplementation;
use Sheba\Dal\AttendanceActionLog\Actions;
use Sheba\ModificationFields;

class AttendanceController extends Controller
{
    use ModificationFields;

    public function takeAction(Request $request, EloquentImplementation $attendance_repository, AttendanceAction $attendance_action)
    {
        $this->validate($request, ['action' => 'required|string|in:' . implode(',', Actions::get())]);
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        /** @var BusinessMember $business_member */
        $business_member = BusinessMember::find($business_member['id']);
        $this->setModifier($business_member->member);
        $attendance_action->setBusinessMember($business_member)->setAction($request->action);
        if (!$attendance_action->canTakeThisAction()) return api_response($request, null, 403);
        $action = $attendance_action->doAction();
        if ($action) return api_response($request, $action, 200);
        return api_response($request, null, 500);
    }
}
