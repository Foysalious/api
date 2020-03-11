<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Sheba\Business\Attendance\MonthlyStat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Business\Attendance\AttendanceList;
use Sheba\Business\Attendance\Monthly\Excel;
use Sheba\Business\Attendance\Member\Excel as MemberMonthlyExcel;
use Sheba\Business\Attendance\Monthly\Stat;
use Sheba\Dal\Attendance\Contract as AttendanceRepoInterface;
use Sheba\Dal\Attendance\Statuses;
use Sheba\Dal\BusinessHoliday\Contract as BusinessHolidayRepoInterface;
use Sheba\Dal\BusinessWeekend\Contract as BusinessWeekendRepoInterface;
use Sheba\Helpers\TimeFrame;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Throwable;

class AttendanceController extends Controller
{

    public function getDailyStats($business, Request $request, AttendanceList $stat)
    {
        $this->validate($request, [
            'status' => 'string|in:' . implode(',', Statuses::get()),
            'department_id' => 'numeric',
            'date' => 'date|date_format:Y-m-d',
        ]);
        $date = $request->has('date') ? Carbon::parse($request->date) : Carbon::now();
        $attendances = $stat->setBusiness($request->business)->setStartDate($date)->setEndDate($date)
            ->setBusinessDepartment($request->department_id)->setStatus($request->status)->get();
        $count = count($attendances);
        if ($count == 0) return api_response($request, null, 404);
        return api_response($request, null, 200, ['attendances' => $attendances, 'total' => $count]);
    }

    public function getMonthlyStats($business, Request $request, AttendanceRepoInterface $attendance_repo, TimeFrame $time_frame, BusinessHolidayRepoInterface $business_holiday_repo,
                                    BusinessWeekendRepoInterface $business_weekend_repo, Excel $monthly_excel)
    {
        try {
            $this->validate($request, ['file' => 'string|in:excel']);
            list($offset, $limit) = calculatePagination($request);
            $business = Business::where('id', (int)$business)->select('id', 'name', 'phone', 'email', 'type')->first();
            $members = $business->members()->select('members.id', 'profile_id')->with(['profile' => function ($q) {
                $q->select('profiles.id', 'name', 'mobile', 'email');
            }, 'businessMember' => function ($q) {
                $q->select('business_member.id', 'business_id', 'member_id', 'type', 'business_role_id')->with(['role' => function ($q) {
                    $q->select('business_roles.id', 'business_department_id', 'name')->with(['businessDepartment' => function ($q) {
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
            $total_members = $members->count();
            if ($request->has('limit')) $members = $members->splice($offset, $limit);

            $all_employee_attendance = [];

            $year = (int)date('Y');
            $month = (int)date('m');
            if ($request->has('month')) $month = $request->month;

            $business_holiday = $business_holiday_repo->getAllByBusiness($business);
            $business_weekend = $business_weekend_repo->getAllByBusiness($business);

            foreach ($members as $member) {
                $member_name = $member->getIdentityAttribute();
                $business_member = $member->businessMember;
                $member_department = $business_member->department() ? $business_member->department() : null;
                $department_name = $member_department ? $member_department->name : 'N/S';
                $department_id = $member_department ? $member_department->id : 'N/S';

                $time_frame = $time_frame->forAMonth($month, $year);
                $time_frame->end = $this->isShowRunningMonthsAttendance($year, $month) ? Carbon::now() : $time_frame->end;
                $attendances = $attendance_repo->getAllAttendanceByBusinessMemberFilteredWithYearMonth($business_member, $time_frame);
                $employee_attendance = (new MonthlyStat($time_frame, $business_holiday, $business_weekend, false))->transform($attendances);

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

            if (count($all_employee_attendance) > 0) {
                if ($request->file == 'excel') {
                    return $monthly_excel->setMonthlyData($all_employee_attendance)->get();
                }
                return api_response($request, $all_employee_attendance, 200, [
                    'all_employee_attendance' => $all_employee_attendance,
                    'total_members' => $total_members,
                ]);
            } else  return api_response($request, null, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
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


        $business_holiday = $business_holiday_repo->getAllByBusiness($business);
        $business_weekend = $business_weekend_repo->getAllByBusiness($business);
        $time_frame = $time_frame->forAMonth($month, date('Y'));
        $attendances = $attendance_repo->getAllAttendanceByBusinessMemberFilteredWithYearMonth($business_member, $time_frame);
        $employee_attendance = (new MonthlyStat($time_frame, $business_holiday, $business_weekend))->transform($attendances);
        $daily_breakdowns = collect($employee_attendance['daily_breakdown']);
        $daily_breakdowns = $daily_breakdowns->filter(function ($breakdown){
            return Carbon::parse($breakdown['date'])->lessThanOrEqualTo(Carbon::today());
        });

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
                'designation' => $business_member->role ? $business_member->role->name : null,
                'department' => $business_member->role && $business_member->role->businessDepartment ? $business_member->role->businessDepartment->name : null,
            ]
        ]);
    }
}