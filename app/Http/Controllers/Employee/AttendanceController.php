<?php namespace App\Http\Controllers\Employee;

use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionProcessor;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionChecker;
use Sheba\Dal\Attendance\Contract as AttendanceRepoInterface;
use Sheba\Business\AttendanceActionLog\AttendanceAction;
use App\Transformers\Business\AttendanceTransformer;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Actions;
use App\Transformers\CustomSerializer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use League\Fractal\Resource\Item;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use Sheba\Helpers\TimeFrame;
use League\Fractal\Manager;
use App\Models\Business;
use Carbon\Carbon;
use Throwable;

class AttendanceController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @param AttendanceRepoInterface $attendance_repo
     * @param TimeFrame $time_frame
     * @param BusinessHolidayRepoInterface $business_holiday_repo
     * @param BusinessWeekendRepoInterface $business_weekend_repo
     * @return JsonResponse
     */
    public function index(Request $request, AttendanceRepoInterface $attendance_repo, TimeFrame $time_frame, BusinessHolidayRepoInterface $business_holiday_repo,
                          BusinessWeekendRepoInterface $business_weekend_repo)
    {
        $this->validate($request, ['year' => 'required|string', 'month' => 'required|string']);
        $year = $request->year;
        $month = $request->month;
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $time_frame = $time_frame->forAMonth($month, $year);
        $business_member_leave = $business_member->leaves()->accepted()->between($time_frame)->get();
        $time_frame->end = $this->isShowRunningMonthsAttendance($year, $month) ? Carbon::now() : $time_frame->end;
        $attendances = $attendance_repo->getAllAttendanceByBusinessMemberFilteredWithYearMonth($business_member, $time_frame);

        $business_holiday = $business_holiday_repo->getAllByBusiness($business_member->business);
        $business_weekend = $business_weekend_repo->getAllByBusiness($business_member->business);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($attendances, new AttendanceTransformer($time_frame, $business_holiday, $business_weekend, $business_member_leave));
        $attendances_data = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['attendance' => $attendances_data]);
    }

    /**
     * @param Request $request
     * @param AttendanceAction $attendance_action
     * @param ActionProcessor $action_processor
     * @return JsonResponse
     */
    public function takeAction(Request $request, AttendanceAction $attendance_action, ActionProcessor $action_processor)
    {
        $validation_data = [
            'action' => 'required|string|in:' . implode(',', Actions::get()),
            'device_id' => 'string',
            'user_agent' => 'string'
        ];

        $business_member = $this->getBusinessMember($request);
        $business = $this->getBusiness($request);
        if (!$business_member) return api_response($request, null, 404);

        $checkout = $action_processor->setActionName(Actions::CHECKOUT)->getAction();
        if ($request->action == Actions::CHECKOUT && $checkout->isNoteRequired()) {
            $validation_data += ['note' => 'string|required_if:action,' . Actions::CHECKOUT];
        }
        if ($business->isRemoteAttendanceEnable()) {
            $validation_data += ['lat' => 'required|numeric', 'lng' => 'required|numeric'];
        }
        $this->validate($request, $validation_data);
        $this->setModifier($business_member->member);

        $attendance_action->setBusinessMember($business_member)
            ->setAction($request->action)
            ->setBusiness($business_member->business)
            ->setNote($request->note)
            ->setDeviceId($request->device_id)
            ->setLat($request->lat)
            ->setLng($request->lng);
        $action = $attendance_action->doAction();

        return response()->json(['code' => $action->getResultCode(), 'message' => $action->getResultMessage()]);
    }

    /**
     * @param $month
     * @param $year
     * @return bool
     */
    private function isShowRunningMonthsAttendance($year, $month)
    {
        return (Carbon::now()->month == (int)$month && Carbon::now()->year == (int)$year);
    }

    public function getTodaysInfo(Request $request, ActionProcessor $action_processor)
    {
        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        /** @var Attendance $attendance */
        $attendance = $business_member->attendanceOfToday();
        /** @var ActionChecker $checkout */
        $checkout = $action_processor->setActionName(Actions::CHECKOUT)->getAction();
        $business = $this->getBusiness($request);
        $is_remote_enable = $business->isRemoteAttendanceEnable();
        $data = [
            'can_checkin' => !$attendance ? 1 : ($attendance->canTakeThisAction(Actions::CHECKIN) ? 1 : 0),
            'can_checkout' => $attendance && $attendance->canTakeThisAction(Actions::CHECKOUT) ? 1 : 0,
            'is_note_required' => 0,
            'checkin_time' => $attendance ? $attendance->checkin_time : null,
            'checkout_time' => $attendance ? $attendance->checkout_time : null,
            'is_geo_required' => $is_remote_enable ? 1 : 0
        ];
        if ($data['can_checkout']) $data['is_note_required'] = $checkout->isNoteRequired();
        return api_response($request, null, 200, ['attendance' => $data]);
    }

    private function getBusinessMember(Request $request)
    {
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!isset($business_member['id'])) return null;
        return BusinessMember::find($business_member['id']);
    }

    public function getBusiness(Request $request)
    {
        $auth_info = $request->auth_info;
        $business_member = $auth_info['business_member'];
        if (!isset($business_member['business_id'])) return null;
        return Business::findOrFail($business_member['business_id']);
    }

}
