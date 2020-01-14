<?php namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\BusinessMember;
use App\Transformers\Business\Attendance\ReportTransformer;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;
use Sheba\Business\Attendance\AttendanceAction;
use Sheba\Dal\Attendance\EloquentImplementation;
use Sheba\Dal\AttendanceActionLog\Actions;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        /*$manager = new Manager();
        $manager->setSerializer(new ArraySerializer());
        $announcements = new Collection($announcements, new ReportTransformer());
        $announcements = $manager->createData($announcements)->toArray()['data'];
        return api_response($request, $announcements, 200, ['announcements' => $announcements]);*/
    }

    public function takeAction(Request $request, EloquentImplementation $attendance_repository, AttendanceAction $attendance_action)
    {
        $this->validate($request, ['action' => 'string|in:' . implode(',', Actions::get())]);
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        /** @var BusinessMember $business_member */
        $business_member = BusinessMember::find($business_member['id']);
        $business_member->attendanceOfToday();
        $attendance_action->setBusinessMember($business_member)->setActionName($request->action_name)->doAction();
        $attendance_repository = $attendance_repository->where('business_member_id', $business_member['id']);
    }
}
