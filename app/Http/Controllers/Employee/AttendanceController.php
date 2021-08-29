<?php namespace App\Http\Controllers\Employee;

use App\Sheba\Business\BusinessBasicInformation;
use Illuminate\Support\Facades\Log;
use Sheba\Business\Attendance\AttendanceCommonInfo;
use Sheba\Dal\AttendanceActionLog\RemoteMode;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionProcessor;
use Sheba\Business\AttendanceActionLog\ActionChecker\ActionChecker;
use Sheba\Dal\Attendance\Contract as AttendanceRepoInterface;
use Sheba\Business\AttendanceActionLog\AttendanceAction;
use App\Transformers\Business\AttendanceTransformer;
use App\Sheba\Business\Attendance\Note\Updater as AttendanceNoteUpdater;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\Attendance\Model as Attendance;
use Sheba\Dal\AttendanceActionLog\Actions;
use App\Transformers\CustomSerializer;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use League\Fractal\Resource\Item;
use App\Models\BusinessMember;
use Sheba\Location\Geo;
use Sheba\Map\Client\BarikoiClient;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use Sheba\Helpers\TimeFrame;
use League\Fractal\Manager;
use App\Models\Business;
use Carbon\Carbon;
use Throwable;

class AttendanceController extends Controller
{
    use ModificationFields, BusinessBasicInformation;

    /**
     * @param Request $request
     * @param AttendanceRepoInterface $attendance_repo
     * @param TimeFrame $time_frame
     * @param BusinessHolidayRepoInterface $business_holiday_repo
     * @param BusinessWeekendRepoInterface $business_weekend_repo
     * @return JsonResponse
     */
    public function index(Request                      $request, AttendanceRepoInterface $attendance_repo, TimeFrame $time_frame, BusinessHolidayRepoInterface $business_holiday_repo,
                          BusinessWeekendRepoInterface $business_weekend_repo)
    {
        $this->validate($request, ['year' => 'required|string', 'month' => 'required|string']);
        $year = $request->year;
        $month = $request->month;
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);
        $time_frame = $time_frame->forAMonth($month, $year);
        $business_member_joining_date = $business_member->join_date;
        $joining_date = null;
        if ($business_member_joining_date->format('m-Y') === Carbon::now()->month($month)->year($year)->format('m-Y')){
            $joining_date = $business_member_joining_date->format('d F');
            $start_date = $business_member_joining_date;
            $end_date = Carbon::now()->month($month)->year($year)->lastOfMonth();
            $time_frame = $time_frame->forDateRange($start_date, $end_date);
        }
        $business_member_leave = $business_member->leaves()->accepted()->between($time_frame)->get();
        $time_frame->end = $this->isShowRunningMonthsAttendance($year, $month) ? Carbon::now() : $time_frame->end;
        $attendances = $attendance_repo->getAllAttendanceByBusinessMemberFilteredWithYearMonth($business_member, $time_frame);

        $business_holiday = $business_holiday_repo->getAllByBusiness($business_member->business);
        $business_weekend = $business_weekend_repo->getAllByBusiness($business_member->business);

        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($attendances, new AttendanceTransformer($time_frame, $business_holiday, $business_weekend, $business_member_leave));
        $attendances_data = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['attendance' => $attendances_data, 'joining_date' => $joining_date]);
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
            'user_agent' => 'string',
            'is_in_wifi_area' => 'required|numeric'
        ];

        $business_member = $this->getBusinessMember($request);
        /** @var Business $business */
        $business = $this->getBusiness($request);
        if (!$business_member) return api_response($request, null, 404);

        Log::info("Attendance for Employee#$business_member->id, Request#" . json_encode($request->except(['profile', 'auth_info', 'auth_user', 'access_token'])));

        if ($business->isRemoteAttendanceEnable($business_member->id) && !$request->is_in_wifi_area) {
            $validation_data += ['lat' => 'sometimes|required|numeric', 'lng' => 'sometimes|required|numeric'];
            $validation_data += ['remote_mode' => 'required|string|in:' . implode(',', RemoteMode::get())];
        }
        $this->validate($request, $validation_data);
        $this->setModifier($business_member->member);

        $checkin = $action_processor->setActionName(Actions::CHECKIN)->getAction();
        $checkout = $action_processor->setActionName(Actions::CHECKOUT)->getAction();

        $is_note_required = 0;
        if ($request->action == Actions::CHECKIN && $checkin->isLateNoteRequired()) $is_note_required = 1;
        if ($request->action == Actions::CHECKOUT && $checkout->isLeftEarlyNoteRequired()) $is_note_required = 1;

        $attendance_action->setBusinessMember($business_member)
            ->setAction($request->action)
            ->setBusiness($business_member->business)
            ->setDeviceId($request->device_id)
            ->setRemoteMode($request->remote_mode)
            ->setLat($request->lat)
            ->setLng($request->lng);
        $action = $attendance_action->doAction();

        return response()->json(['code' => $action->getResultCode(),
            'is_note_required' => $is_note_required,
            'date' => Carbon::now()->format('jS F Y'),
            'message' => $action->getResultMessage()]);
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
        /** @var Business $business */
        $business = $this->getBusiness($request);
        $is_remote_enable = $business->isRemoteAttendanceEnable($business_member->id);
        $data = [
            'can_checkin' => !$attendance ? 1 : ($attendance->canTakeThisAction(Actions::CHECKIN) ? 1 : 0),
            'can_checkout' => $attendance && $attendance->canTakeThisAction(Actions::CHECKOUT) ? 1 : 0,
            'checkin_time' => $attendance ? $attendance->checkin_time : null,
            'checkout_time' => $attendance ? $attendance->checkout_time : null,
            'is_geo_required' => $is_remote_enable ? 1 : 0,
            'is_remote_enable' => $is_remote_enable
        ];
        return api_response($request, null, 200, ['attendance' => $data]);
    }

    public function attendanceInfo(Request $request, AttendanceCommonInfo $attendance_common_info)
    {
        /** @var Business $business */
        $business = $this->getBusiness($request);
        $attendance_common_info->setLat($request->lat)->setLng($request->lng);
        $is_in_wifi_area = $attendance_common_info->isInWifiArea($business) ? 1 : 0;
        $data = [
            'is_in_wifi_area' => $is_in_wifi_area,
            'which_office' => $is_in_wifi_area ? $attendance_common_info->whichOffice($business) : null,
            'address' => $attendance_common_info->getAddress()
        ];

        return api_response($request, null, 200, ['info' => $data]);
    }

    /**
     * @param Request $request
     * @param AttendanceNoteUpdater $note_updater
     * @return JsonResponse
     */
    public function storeNote(Request $request, AttendanceNoteUpdater $note_updater)
    {
        $validation_data = [
            'action' => 'required|string|in:' . implode(',', Actions::get()),
            'note' => 'required|string'
        ];
        $this->validate($request, $validation_data);

        /** @var BusinessMember $business_member */
        $business_member = $this->getBusinessMember($request);
        if (!$business_member) return api_response($request, null, 404);

        $note_updater->setBusinessMember($business_member)
            ->setAction($request->action)
            ->setNote($request->note)
            ->updateNote();
        return api_response($request, null, 200);
    }

}
