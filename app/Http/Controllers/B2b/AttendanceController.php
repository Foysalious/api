<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\Attendance\MonthlyStat;
use App\Sheba\Business\BusinessBasicInformation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Sheba\Business\Attendance\AttendanceList;
use Sheba\Business\Attendance\Daily\DailyExcel;
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
use Sheba\Business\Attendance\HalfDaySetting\Updater as HalfDaySettingUpdater;
use Sheba\Business\Attendance\Detail\DetailsExcel as DetailsExcel;
use Throwable;

class AttendanceController extends Controller
{
    use ModificationFields, BusinessBasicInformation;

    /** @var BusinessHolidayRepoInterface $holidayRepository */
    private $holidayRepository;

    /**
     * AttendanceController constructor.
     * @param BusinessHolidayRepoInterface $business_holidays_repo
     */
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
     * @param BusinessOfficeRepoInterface $business_office_repo
     * @return JsonResponse
     */
    public function getDailyStats($business, Request $request, AttendanceList $stat, TimeFrame $time_frame,
                                  BusinessOfficeRepoInterface $business_office_repo)
    {
        $this->validate($request, [
            'status' => 'string|in:' . implode(',', Statuses::get()),
            'department_id' => 'numeric',
            'date' => 'date|date_format:Y-m-d',
        ]);

        $date = $request->has('date') ? Carbon::parse($request->date) : Carbon::now();
        $selected_date = $time_frame->forADay($date);

        $checkin_location = $checkout_location = null;
        if ($request->checkin_location) $checkin_location = $this->getIpById($request->checkin_location, $business_office_repo);
        if ($request->checkout_location) $checkout_location = $this->getIpById($request->checkout_location, $business_office_repo);

        /** @var array $attendances */
        $attendances = $stat->setBusiness($request->business)
            ->setSelectedDate($selected_date)
            ->setBusinessDepartment($request->department_id)
            ->setSearch($request->search)
            ->setCheckinStatus($request->checkin_status)
            ->setCheckoutStatus($request->checkout_status)
            ->setSortKey($request->sort)
            ->setSortColumn($request->sort_column)
            ->setStatusFilter($request->status_filter)
            ->setOfficeOrRemoteCheckin($request->checkin_office_or_remote)
            ->setOfficeOrRemoteCheckout($request->checkout_office_or_remote)
            ->setCheckinLocation($checkin_location)
            ->setCheckoutLocation($checkout_location)
            ->get();

        $count = count($attendances);
        if ($request->file == 'excel') return (new DailyExcel())->setDate($date->format('Y-m-d'))->setData($attendances)->download();
        return api_response($request, null, 200, ['attendances' => $attendances, 'total' => $count]);
    }

    /**
     * @param $business
     * @param Request $request
     * @param AttendanceRepoInterface $attendance_repo
     * @param TimeFrame $time_frame
     * @param BusinessHolidayRepoInterface $business_holiday_repo
     * @param BusinessWeekendRepoInterface $business_weekend_repo
     * @param Excel $monthly_excel
     * @return JsonResponse|void
     */
    public function getMonthlyStats($business, Request $request, AttendanceRepoInterface $attendance_repo,
                                    TimeFrame $time_frame, BusinessHolidayRepoInterface $business_holiday_repo,
                                    BusinessWeekendRepoInterface $business_weekend_repo, Excel $monthly_excel)
    {
        ini_set('memory_limit', '6096M');
        ini_set('max_execution_time', 480);

        $this->validate($request, ['file' => 'string|in:excel']);
        list($offset, $limit) = calculatePagination($request);
        /** @var Business $business */
        $business = Business::where('id', (int)$business)->select('id', 'name', 'phone', 'email', 'type')->first();

        $business_members = $business->getAccessibleBusinessMember();

        if ($request->has('department_id')) {
            $business_members = $business_members->whereHas('role', function ($q) use ($request) {
                $q->whereHas('businessDepartment', function ($q) use ($request) {
                    $q->where('business_departments.id', $request->department_id);
                });
            });
        }

        $all_employee_attendance = [];
        if ($request->has('start_date') && $request->has('end_date')) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        } else {
            $start_date = Carbon::now()->startOfMonth()->toDateString();
            $end_date = Carbon::now()->endOfMonth()->toDateString();
        }

        $business_holiday = $business_holiday_repo->getAllByBusiness($business);
        $business_weekend = $business_weekend_repo->getAllByBusiness($business);
        foreach ($business_members->get() as $business_member) {
            $member_name = $business_member->member->profile->name;
            /** @var BusinessMember $business_member */
            $member_department = $business_member->role ? $business_member->role->businessDepartment : null;
            $department_name = $member_department ? $member_department->name : 'N/S';
            $department_id = $member_department ? $member_department->id : 'N/S';

            $time_frame = $time_frame->forDateRange($start_date, $end_date);
            $business_member_leave = $business_member->leaves()->accepted()->startDateBetween($time_frame)->endDateBetween($time_frame)->get();
            $attendances = $attendance_repo->getAllAttendanceByBusinessMemberFilteredWithYearMonth($business_member, $time_frame);
            $employee_attendance = (new MonthlyStat($time_frame, $business_holiday, $business_weekend, $business_member_leave, false))->transform($attendances);

            array_push($all_employee_attendance, [
                'business_member_id' => $business_member->id,
                'employee_id' => $business_member->employee_id ? $business_member->employee_id : 'N/A',
                'member' => [
                    'id' => $business_member->member->id,
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
        if ($request->has('sort_on_absent')) $all_employee_attendance = $this->attendanceSortOnAbsent($all_employee_attendance, $request->sort_on_absent);
        if ($request->has('sort_on_present')) $all_employee_attendance = $this->attendanceSortOnPresent($all_employee_attendance, $request->sort_on_present);
        if ($request->has('sort_on_leave')) $all_employee_attendance = $this->attendanceSortOnLeave($all_employee_attendance, $request->sort_on_leave);
        if ($request->has('sort_on_late')) $all_employee_attendance = $this->attendanceSortOnLate($all_employee_attendance, $request->sort_on_late);

        $total_members = $all_employee_attendance->count();
        if ($request->has('limit')) $all_employee_attendance = $all_employee_attendance->splice($offset, $limit);
        if ($all_employee_attendance->isEmpty()) return api_response($request, null, 404);
        if ($request->file == 'excel') return $monthly_excel->setMonthlyData($all_employee_attendance->toArray())->get();

        return api_response($request, $all_employee_attendance, 200, ['all_employee_attendance' => $all_employee_attendance, 'total_members' => $total_members]);
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
     * @param $employee_attendance
     * @param string $sort
     * @return mixed
     */
    private function attendanceSortOnLate($employee_attendance, $sort = 'asc')
    {
        $sort_by = ($sort === 'asc') ? 'sortBy' : 'sortByDesc';
        return $employee_attendance->$sort_by(function ($attendance, $key) {
            return strtoupper($attendance['attendance']['late']);
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

    /**
     * @param $business
     * @param $member
     * @param Request $request
     * @param BusinessHolidayRepoInterface $business_holiday_repo
     * @param BusinessWeekendRepoInterface $business_weekend_repo
     * @param AttendanceRepoInterface $attendance_repo
     * @param BusinessMemberRepositoryInterface $business_member_repository
     * @param TimeFrame $time_frame
     * @param AttendanceList $list
     * @param MemberMonthlyExcel $member_monthly_excel
     * @return JsonResponse|void
     */
    public function showStat($business, $member, Request $request, BusinessHolidayRepoInterface $business_holiday_repo,
                             BusinessWeekendRepoInterface $business_weekend_repo, AttendanceRepoInterface $attendance_repo,
                             BusinessMemberRepositoryInterface $business_member_repository,
                             TimeFrame $time_frame, AttendanceList $list, DetailsExcel $details_excel)
    {
        $this->validate($request, ['month' => 'numeric|min:1|max:12']);
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $business_member_repository->where('business_id', $business->id)->where('member_id', $member)->first();

        $time_frame = $time_frame->forDateRange($request->start_date, $request->end_date);

        $business_member_leave = $business_member->leaves()->accepted()->between($time_frame)->get();

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
            return $details_excel->setBreakDownData($daily_breakdowns->toArray())
                ->setBusinessMember($business_member)
                ->setDepartment($business_member->role && $business_member->role->businessDepartment ? $business_member->role->businessDepartment->name : null)
                ->download();
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
        $half_day_leave_types = $business->leaveTypes()->isHalfDayEnable();
        $data = [
            'office_hour_type' => 'Fixed Time',
            'start_time' => $office_time ? Carbon::parse($office_time->start_time)->format('h:i a') : '09:00 am',
            'end_time' => $office_time ? Carbon::parse($office_time->end_time)->format('h:i a') : '05:00 pm',
            'weekends' => $weekend_days,
            'is_half_day_enable' => $business->is_half_day_enable,
            'half_day_leave_types_count' => $half_day_leave_types->count(),
            'half_day_leave_types' => $half_day_leave_types->pluck('title'),
            'half_day_initial_timings' => $this->getHalfDayTimings($business)
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
            'office_hour_type' => 'required', 'start_time' => 'date_format:H:i:s', 'end_time' => 'date_format:H:i:s|after:start_time', 'weekends' => 'required|array',
            'half_day' => 'required', 'half_day_config' => 'string'
        ], [
            'end_time.after' => 'Start Time Must Be Less Than End Time'
        ]);
        $start_time = Carbon::parse($request->start_time)->format('H:i') . ':59';
        $end_time = Carbon::parse($request->end_time)->format('H:i') . ':59';

        $business_member = $request->business_member;
        $office_timing = $updater->setBusiness($request->business)
            ->setMember($business_member->member)
            ->setOfficeHourType($request->office_hour_type)
            ->setStartTime($start_time)
            ->setEndTime($end_time)
            ->setWeekends($request->weekends)
            ->setHalfDayTimings($request)
            ->update();

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
        $business_offices = $business_office_repo->getAllByBusiness($business);
        $attendance_types = $business->attendanceTypes()->withTrashed()->get();
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
                                            SettingCreator $creator, SettingUpdater $updater, SettingDeleter $deleter)
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
                    $creator->resetError();
                    return;
                }
                $creator->create();
            });

            $edited_offices = $offices->where('action', ActionType::EDIT);
            $edited_offices->each(function ($edited_office) use ($updater, $business, &$errors) {
                $updater->setBusinessOfficeId($edited_office->id)->setName($edited_office->name)->setIp($edited_office->ip);
                if ($updater->hasError()) {
                    array_push($errors, $updater->getErrorMessage());
                    $updater->resetError();
                    return;
                }
                $updater->update();
            });
        }

        if ($errors) {
            if ($this->isFailedToUpdateAllSettings($errors, $business_offices))
                return api_response($request, null, 422, ['message' => implode(', ', $errors)]);

            return api_response($request, null, 303, ['message' => implode(', ', $errors)]);
        }

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

    /**
     * @param array $errors
     * @param $business_offices
     * @return bool
     */
    private function isFailedToUpdateAllSettings(array $errors, $business_offices)
    {
        return count($errors) == count($business_offices);
    }

    private function getHalfDayTimings(Business $business)
    {
        if ($business->half_day_configuration) {
            $half_day_times = json_decode($business->half_day_configuration);
            return [
                'first_half' => [
                    'start_time' => Carbon::parse($half_day_times->first_half->start_time)->format('h:i a'),
                    'end_time' => Carbon::parse($half_day_times->first_half->end_time)->format('h:i a')
                ],
                'second_half' => [
                    'start_time' => Carbon::parse($half_day_times->second_half->start_time)->format('h:i a'),
                    'end_time' => Carbon::parse($half_day_times->second_half->end_time)->format('h:i a')
                ]
            ];
        } else {
            return [
                'first_half' => [
                    'start_time' => '09:00 am',
                    'end_time' => '12:59 pm'
                ],
                'second_half' => [
                    'start_time' => '01:00 pm',
                    'end_time' => '06:00 pm'
                ]
            ];
        }
    }

    public function updateHalfDaySetting($business, Request $request, HalfDaySettingUpdater $updater)
    {
        $this->validate($request, ['half_day' => 'required', 'half_day_config' => 'required|string']);

        $business_member = $request->business_member;
        $business = $request->business;
        $this->setModifier($business_member->member);

        $updater->setBusiness($business)->setHalfDayConfig($request->half_day_config)->update();

        return api_response($request, null, 200, ['message' => "Update Successful"]);
    }


    public function getAllHolidayDates(Request $request)
    {
        $holiday_list = new HolidayList($request->business, $this->holidayRepository);
        $holidays = $holiday_list->getAllHolidayDates($request);

        return api_response($request, null, 200, ['business_holidays' => array_values($holidays)]);
    }

    /**
     * @param $location
     * @param BusinessOfficeRepoInterface $business_office_repo
     * @return mixed
     */
    public function getIpById($location, BusinessOfficeRepoInterface $business_office_repo)
    {
        $business_office = $business_office_repo->find($location);
        return $business_office ? $business_office->ip : null;
    }
}
