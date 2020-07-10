<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\Attendance\MonthlyStat;
use App\Sheba\Business\BusinessBasicInformation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Business\Attendance\AttendanceList;
use Sheba\Business\Attendance\Monthly\Excel;
use Sheba\Business\Attendance\Member\Excel as MemberMonthlyExcel;
use Sheba\Business\Attendance\Setting\ActionType;
use Sheba\Dal\Attendance\Contract as AttendanceRepoInterface;
use Sheba\Dal\Attendance\Statuses;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Dal\BusinessOfficeHours\Contract as BusinessOfficeHoursRepoInterface;
use Sheba\Dal\BusinessAttendanceTypes\Contract as BusinessAttendanceTypesRepoInterface;
use Sheba\Dal\BusinessOffice\Contract as BusinessOfficeRepoInterface;
use Sheba\Helpers\TimeFrame;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Business\OfficeTiming\Updater as OfficeTimingUpdater;
use Sheba\Business\Attendance\Setting\Creator as SettingCreator;
use Sheba\Business\Attendance\Setting\Updater as SettingUpdater;
use Sheba\Business\Attendance\Setting\Deleter as SettingDeleter;
use Sheba\Business\Attendance\Setting\AttendanceSettingTransformer;
use Sheba\Business\Attendance\Type\Updater as TypeUpdater;
use Sheba\Business\Holiday\HolidayList;
use Sheba\Business\Holiday\Creator as HolidayCreator;
use Sheba\Business\Holiday\Updater as HolidayUpdater;
use Sheba\Business\Holiday\CreateRequest as HolidayCreatorRequest;
use Throwable;

class AttendanceController extends Controller
{
    use ModificationFields, BusinessBasicInformation;

    /** @var BusinessHolidayRepoInterface $holidayRepository */
    private $holidayRepository;

    public function __construct(BusinessHolidayRepoInterface $business_holidays_repo)
    {
        $this->holidayRepository = $business_holidays_repo;
        return $this;
    }

    /**
     * @param $business
     * @param Request $request
     * @param AttendanceList $stat
     * @param TimeFrame $time_frame
     * @return JsonResponse
     */
    public function getDailyStats($business, Request $request, AttendanceList $stat, TimeFrame $time_frame)
    {
        $this->validate($request, [
            'status' => 'string|in:' . implode(',', Statuses::get()),
            'department_id' => 'numeric',
            'date' => 'date|date_format:Y-m-d',
        ]);
        $date = $request->has('date') ? Carbon::parse($request->date) : Carbon::now();
        $selected_date = $time_frame->forADay($date);

        $attendances = $stat->setBusiness($request->business)
            ->setSelectedDate($selected_date)
            ->setBusinessDepartment($request->department_id)->setStatus($request->status)->setSearch($request->search)
            ->setCheckinStatus($request->checkin_status)->setCheckoutStatus($request->checkout_status)
            ->setSortKey($request->sort)->setSortColumn($request->sort_column)
            ->get();

        $count = count($attendances);
        if (!$count) return api_response($request, null, 404);

        return api_response($request, null, 200, ['attendances' => $attendances, 'total' => $count]);
    }

    public function getMonthlyStats($business, Request $request, AttendanceRepoInterface $attendance_repo,
                                    TimeFrame $time_frame, BusinessHolidayRepoInterface $business_holiday_repo,
                                    BusinessWeekendRepoInterface $business_weekend_repo, Excel $monthly_excel)
    {
        $this->validate($request, ['file' => 'string|in:excel']);
        list($offset, $limit) = calculatePagination($request);
        $business = Business::where('id', (int)$business)->select('id', 'name', 'phone', 'email', 'type')->first();
        $members = $business->members()->select('members.id', 'profile_id')->with([
            'profile' => function ($q) {
                $q->select('profiles.id', 'name', 'mobile', 'email');
            },
            'businessMember' => function ($q) {
                $q->select('business_member.id', 'business_id', 'member_id', 'type', 'business_role_id')->with([
                    'role' => function ($q) {
                        $q->select('business_roles.id', 'business_department_id', 'name')->with([
                            'businessDepartment' => function ($q) {
                                $q->select('business_departments.id', 'business_id', 'name');
                            }]);
                    }]);
            }]);

        if ($request->has('department_id')) {
            $members = $members->whereHas('businessMember', function ($q) use ($request) {
                $q->whereHas('role', function ($q) use ($request) {
                    $q->whereHas('businessDepartment', function ($q) use ($request) {
                        $q->where('business_departments.id', $request->department_id);
                    });
                });
            });
        }

        $members = $members->get();
        $all_employee_attendance = [];

        $year = (int)date('Y');
        $month = (int)date('m');
        if ($request->has('month')) $month = $request->month;

        $business_holiday = $business_holiday_repo->getAllByBusiness($business);
        $business_weekend = $business_weekend_repo->getAllByBusiness($business);
        foreach ($members as $member) {
            $member_name = $member->getIdentityAttribute();
            /** @var BusinessMember $business_member */
            $business_member = $member->businessMember;
            $member_department = $business_member->department() ? $business_member->department() : null;
            $department_name = $member_department ? $member_department->name : 'N/S';
            $department_id = $member_department ? $member_department->id : 'N/S';

            $time_frame = $time_frame->forAMonth($month, $year);
            $business_member_leave = $business_member->leaves()->accepted()->startDateBetween($time_frame)->endDateBetween($time_frame)->get();
            $time_frame->end = $this->isShowRunningMonthsAttendance($year, $month) ? Carbon::now() : $time_frame->end;
            $attendances = $attendance_repo->getAllAttendanceByBusinessMemberFilteredWithYearMonth($business_member, $time_frame);
            $employee_attendance = (new MonthlyStat($time_frame, $business_holiday, $business_weekend, $business_member_leave, false))->transform($attendances);

            array_push($all_employee_attendance, [
                'business_member_id' => $business_member->id,
                'member' => [
                    'id' => $member->id,
                    'name' => $member_name,
                ],
                'department' => [
                    'id' => $department_id,
                    'name' => $department_name,
                ],
                'attendance' => $employee_attendance['statistics']
            ]);
        }
        $all_employee_attendance = collect($all_employee_attendance);
        if ($request->has('search')) $all_employee_attendance = $this->searchWithEmployeeName($all_employee_attendance, $request);

        if ($request->has('sort_on_absent')) {
            $all_employee_attendance = $this->attendanceSortOnAbsent($all_employee_attendance, $request->sort_on_absent);
        }
        if ($request->has('sort_on_present')) {
            $all_employee_attendance = $this->attendanceSortOnPresent($all_employee_attendance, $request->sort_on_present);
        }
        if ($request->has('sort_on_leave')) {
            $all_employee_attendance = $this->attendanceSortOnLeave($all_employee_attendance, $request->sort_on_leave);
        }

        $total_members = $all_employee_attendance->count();
        if ($request->has('limit')) $all_employee_attendance = $all_employee_attendance->splice($offset, $limit);

        if (count($all_employee_attendance) > 0) {
            if ($request->file == 'excel') {
                return $monthly_excel->setMonthlyData($all_employee_attendance->toArray())->get();
            }
            return api_response($request, $all_employee_attendance, 200, [
                'all_employee_attendance' => $all_employee_attendance,
                'total_members' => $total_members,
            ]);
        } else  return api_response($request, null, 404);
    }

    /**
     * @param $employee_attendance
     * @param Request $request
     * @return mixed
     */
    private function searchWithEmployeeName($employee_attendance, Request $request)
    {
        return $employee_attendance->filter(function ($attendance) use ($request) {
            return str_contains(strtoupper($attendance['member']['name']), strtoupper($request->search));
        });
    }

    /**
     * @param $employee_attendance
     * @param string $sort
     * @return mixed
     */
    private function attendanceSortOnAbsent($employee_attendance, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $employee_attendance->$sort_by(function ($attendance, $key) {
            return strtoupper($attendance['attendance']['absent']);
        });
    }

    /**
     * @param $employee_attendance
     * @param string $sort
     * @return mixed
     */
    private function attendanceSortOnPresent($employee_attendance, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $employee_attendance->$sort_by(function ($attendance, $key) {
            return strtoupper($attendance['attendance']['present']);
        });
    }

    /**
     * @param $employee_attendance
     * @param string $sort
     * @return mixed
     */
    private function attendanceSortOnLeave($employee_attendance, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $employee_attendance->$sort_by(function ($attendance, $key) {
            return strtoupper($attendance['attendance']['on_leave']);
        });
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

    public function showStat($business, $member, Request $request, BusinessHolidayRepoInterface $business_holiday_repo,
                             BusinessWeekendRepoInterface $business_weekend_repo, AttendanceRepoInterface $attendance_repo,
                             BusinessMemberRepositoryInterface $business_member_repository,
                             TimeFrame $time_frame, AttendanceList $list, MemberMonthlyExcel $member_monthly_excel)
    {
        $this->validate($request, ['month' => 'numeric|min:1|max:12']);
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $business_member_repository->where('business_id', $business->id)->where('member_id', $member)->first();
        $month = $request->has('month') ? $request->month : date('m');

        $time_frame = $time_frame->forAMonth($month, date('Y'));
        $business_member_leave = $business_member->leaves()->accepted()->between($time_frame)->get();
        $time_frame->end = $this->isShowRunningMonthsAttendance(date('Y'), $month) ? Carbon::now() : $time_frame->end;
        $attendances = $attendance_repo->getAllAttendanceByBusinessMemberFilteredWithYearMonth($business_member, $time_frame);

        $business_holiday = $business_holiday_repo->getAllByBusiness($business);
        $business_weekend = $business_weekend_repo->getAllByBusiness($business);

        $employee_attendance = (new MonthlyStat($time_frame, $business_holiday, $business_weekend, $business_member_leave))->transform($attendances);
        $daily_breakdowns = collect($employee_attendance['daily_breakdown']);
        $daily_breakdowns = $daily_breakdowns->filter(function ($breakdown) {
            return Carbon::parse($breakdown['date'])->lessThanOrEqualTo(Carbon::today());
        });

        if ($request->has('sort_on_date')) $daily_breakdowns = $this->attendanceSortOnDate($daily_breakdowns, $request->sort_on_date)->values();
        if ($request->has('sort_on_hour')) $daily_breakdowns = $this->attendanceSortOnHour($daily_breakdowns, $request->sort_on_hour)->values();
        if ($request->has('sort_on_checkin')) $daily_breakdowns = $this->attendanceSortOnCheckin($daily_breakdowns, $request->sort_on_checkin)->values();
        if ($request->has('sort_on_checkout')) $daily_breakdowns = $this->attendanceSortOnCheckout($daily_breakdowns, $request->sort_on_checkout)->values();

        if ($request->file == 'excel') {
            return $member_monthly_excel->setMonthlyData($daily_breakdowns->toArray())
                ->setMember($business_member->member)
                ->setDesignation($business_member->role ? $business_member->role->name : null)
                ->setDepartment($business_member->role && $business_member->role->businessDepartment ? $business_member->role->businessDepartment->name : null)
                ->get();
        }

        return api_response($request, $list, 200, [
            'stat' => $employee_attendance['statistics'],
            'attendances' => $daily_breakdowns,
            'employee' => [
                'id' => $business_member->member->id,
                'name' => $business_member->member->profile->name,
                'image' => $business_member->member->profile->pro_pic,
                'mobile' => $business_member->member->profile->mobile ?: 'N/S',
                'designation' => $business_member->role ? $business_member->role->name : null,
                'department' => $business_member->role && $business_member->role->businessDepartment ? $business_member->role->businessDepartment->name : null,
            ]
        ]);
    }

    /**
     * @param $business
     * @param Request $request
     * @param BusinessWeekendRepoInterface $business_weekend_repo
     * @param BusinessOfficeHoursRepoInterface $office_hours
     * @return JsonResponse
     */
    public function getOfficeTime($business, Request $request, BusinessWeekendRepoInterface $business_weekend_repo, BusinessOfficeHoursRepoInterface $office_hours)
    {
        $business = $request->business;
        $weekends = $business_weekend_repo->getAllByBusiness($business);
        $weekend_days = $weekends->pluck('weekday_name')->toArray();
        $weekend_days = array_map('ucfirst', $weekend_days);
        $office_time = $office_hours->getOfficeTime($business);
        $data = [
            'office_hour_type' => 'Fixed Time', 'start_time' => Carbon::parse($office_time->start_time)->format('h:i a'), 'end_time' => Carbon::parse($office_time->end_time)->format('h:i a'), 'weekends' => $weekend_days
        ];
        return api_response($request, null, 200, ['office_timing' => $data]);
    }

    /**
     * @param Request $request
     * @param OfficeTimingUpdater $updater
     * @return JsonResponse
     */
    public function updateOfficeTime(Request $request, OfficeTimingUpdater $updater)
    {
        $this->validate($request, [
            'office_hour_type' => 'required', 'start_time' => 'date_format:H:i:s', 'end_time' => 'date_format:H:i:s|after:start_time', 'weekends' => 'required|array'
        ],[
          'end_time.after' => 'Start Time Must Be Less Than End Time'
        ]);
        $business_member = $request->business_member;
        $office_timing = $updater->setBusiness($request->business)->setMember($business_member->member)->setOfficeHourType($request->office_hour_type)->setStartTime($request->start_time)->setEndTime($request->end_time)->setWeekends($request->weekends)->update();

        if ($office_timing) return api_response($request, null, 200, ['msg' => "Update Successful"]);
    }

    /**
     * @param $business
     * @param Request $request
     * @param BusinessAttendanceTypesRepoInterface $attendance_types_repo
     * @param BusinessOfficeRepoInterface $business_office_repo
     * @param AttendanceSettingTransformer $transformer
     * @return JsonResponse
     */
    public function getAttendanceSetting($business, Request $request,
                                         BusinessAttendanceTypesRepoInterface $attendance_types_repo,
                                         BusinessOfficeRepoInterface $business_office_repo, AttendanceSettingTransformer $transformer)
    {
        $business = $request->business;
        $attendance_types = $business->attendanceTypes()->withTrashed()->get();
        $business_offices = $business_office_repo->getAllByBusiness($business);
        $attendance_setting_data = $transformer->getData($attendance_types, $business_offices);

        return api_response($request, null, 200, [
            'sheba_attendance_types' => $attendance_setting_data["sheba_attendance_types"],
            'business_attendance_types' => $attendance_setting_data["attendance_types"],
            'business_offices' => $attendance_setting_data["business_offices"]
        ]);
    }

    /**
     * @param $business
     * @param Request $request
     * @param TypeUpdater $type_updater
     * @param SettingCreator $creator
     * @param SettingUpdater $updater
     * @param SettingDeleter $deleter
     * @return JsonResponse
     */
    public function updateAttendanceSetting($business, Request $request, TypeUpdater $type_updater,
                                            SettingCreator $creator, SettingUpdater $updater, SettingDeleter $deleter
)
    {
        $this->validate($request, ['attendance_types' => 'required|string', 'business_offices' => 'required|string']);

        $business_member = $request->business_member;
        $business = $request->business;
        $this->setModifier($business_member->member);
        $errors = [];

        $attendance_types = json_decode($request->attendance_types);
        if (!is_null($attendance_types)) {
            foreach ($attendance_types as $attendance_type) {
                $attendance_type_id = isset($attendance_type->id) ? $attendance_type->id : null;

                $type_updater->setBusiness($business)
                    ->setTypeId($attendance_type_id)
                    ->setType($attendance_type->type)
                    ->setAction($attendance_type->action)
                    ->update();
            }
        }

        $business_offices = json_decode($request->business_offices);
        if (!is_null($business_offices)) {
            $offices = collect($business_offices);
            $deleted_offices = $offices->where('action', ActionType::DELETE);
            $deleted_offices->each(function ($deleted_office) use ($deleter) {
                $deleter->setBusinessOfficeId($deleted_office->id);
                $deleter->delete();
            });

            $added_offices = $offices->where('action', ActionType::ADD);
            $added_offices->each(function ($added_office) use ($creator, $business, &$errors) {
                $creator->setBusiness($business)->setName($added_office->name)->setIp($added_office->ip);
                if ($creator->hasError()) {
                    array_push($errors, $creator->getErrorMessage());
                    return;
                }
                $creator->create();
            });

            $edited_offices = $offices->where('action', ActionType::EDIT);
            $edited_offices->each(function ($edited_office) use ($updater, $business, &$errors) {
                $updater->setBusinessOfficeId($edited_office->id)->setName($edited_office->name)->setIp($edited_office->ip);
                if ($updater->hasError()) {
                    array_push($errors, $updater->getErrorMessage());
                    return;
                }
                $updater->update();
            });
        }

        if ($errors) return api_response($request, null, 303, ['message' => implode(', ', $errors)]);

        return api_response($request, null, 200, ['message' => "Update Successful"]);
    }

    public function getHolidays(Request $request)
    {
        $holiday_list = new HolidayList($request->business, $this->holidayRepository);
        $holidays = $holiday_list->getHolidays($request);

        return api_response($request, null, 200, [
            'business_holidays' => $holidays
        ]);
    }

    public function storeHoliday(Request $request, HolidayCreator $creator)
    {
        $this->validate($request, [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'title' => 'required|string'
        ]);
        $business_member = $request->business_member;

        $holiday = $creator->setBusiness($request->business)
            ->setMember($business_member->member)
            ->setHolidayRepo($this->holidayRepository)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->setHolidayName($request->title)->create();
        return api_response($request, null, 200, ['holiday' => $holiday]);
    }

    /**
     * @param $business
     * @param $holiday
     * @param Request $request
     * @param HolidayCreatorRequest $creator_request
     * @param HolidayUpdater $updater
     * @return JsonResponse
     */
    public function update($business, $holiday, Request $request, HolidayCreatorRequest $creator_request, HolidayUpdater $updater)
    {
        $this->validate($request, [
            'start_date' => 'required|date_format:Y-m-d|',
            'end_date' => 'required|date_format:Y-m-d||after_or_equal:start_date',
            'title' => 'required|string'
        ]);
        $manager_member = $request->manager_member;
        $holiday = $this->holidayRepository->find((int)$holiday);
        $updater_request = $creator_request->setBusiness($request->business)
            ->setMember($manager_member)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->setHolidayName($request->title);
        $updater->setHoliday($holiday)->setBusinessHolidayCreatorRequest($updater_request)->update();
        return api_response($request, null, 200);
    }

    /**
     * @param $business
     * @param $holiday
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy($business, $holiday, Request $request)
    {
        $holiday = $this->holidayRepository->find((int)$holiday);
        if (!$holiday) return api_response($request, null, 404);
        $this->holidayRepository->delete($holiday);
        return api_response($request, null, 200);
    }

    private function attendanceSortOnDate($attendances, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $attendances->$sort_by(function ($attendance, $key) {
            return strtoupper($attendance['date']);
        });
    }

    private function attendanceSortOnHour($attendances, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $attendances->$sort_by(function ($attendance, $key) {
            return strtoupper($attendance['attendance']['active_hours']);
        });
    }

    private function attendanceSortOnCheckin($attendances, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $attendances->$sort_by(function ($attendance, $key) {
            return strtoupper($attendance['attendance']['check_in']['time']);
        });
    }

    private function attendanceSortOnCheckout($attendances, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $attendances->$sort_by(function ($attendance, $key) {
            return strtoupper($attendance['attendance']['check_out']['time']);
        });
    }
}
