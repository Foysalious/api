<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Transformers\Business\EmployeeShiftDetailsTransformer;
use App\Transformers\Business\ShiftCalenderTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use Sheba\Business\ShiftSetting\ShiftAssign\Requester;
use Sheba\Business\ShiftSetting\ShiftAssign\Creator;
use Sheba\Business\ShiftSetting\ShiftAssign\ShiftRemover;
use Sheba\Dal\BusinessShift\BusinessShiftRepository;
use Sheba\Dal\ShiftAssignment\ShiftAssignmentRepository;
use Sheba\ModificationFields;
use League\Fractal\Resource\Item;
use Sheba\Repositories\Interfaces\Business\DepartmentRepositoryInterface;

class ShiftAssignmentController extends Controller
{
    use ModificationFields;
    private $businessShiftRepository;
    private $shiftCalenderRequester;
    /** @var Creator  */
    private $shiftCalenderCreator;
    private $shiftRemover;
    /*** @var DepartmentRepositoryInterface */
    private $departmentRepo;
    /*** @var ShiftAssignmentRepository */
    private $shiftAssignmentRepository;

    public function __construct(DepartmentRepositoryInterface $department_repository)
    {
        $this->shiftAssignmentRepository = app(ShiftAssignmentRepository::class);
        $this->businessShiftRepository = app(BusinessShiftRepository::class);
        $this->shiftCalenderRequester = app(Requester::class);
        $this->shiftCalenderCreator = app(Creator::class);
        $this->shiftRemover = app(ShiftRemover::class);
        $this->departmentRepo = $department_repository;
    }

    public function index(Request $request, ShiftAssignmentRepository $shift_assignment_repository)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        list($offset, $limit) = calculatePagination($request);
        $start_date = $request->start_date ?: Carbon::now()->addDay()->toDateString();
        $end_date = $request->end_date ?: Carbon::now()->addDays(7)->toDateString();
        $active_business_member = $business->getActiveBusinessMember();
        if ($request->has('department_id')) {
            $active_business_member = $active_business_member->whereHas('role', function ($q) use ($request) {
                $q->whereHas('businessDepartment', function ($q) use ($request) {
                    $q->where('business_departments.id', $request->department_id);
                });
            });
        }
        $active_business_member_ids = $active_business_member->pluck('id')->toArray();

        $shift_calender = $shift_assignment_repository->builder()->whereIn('business_member_id', $active_business_member_ids)->whereBetween('date', [$start_date, $end_date]);
        if ($request->has('shift_type')) $shift_calender = $shift_calender->where($request->shift_type, 1);
        $shift_calender_data = (new ShiftCalenderTransformer())->transform($shift_calender->get());
        $shift_calender_employee_data = collect($shift_calender_data['data'])->splice($offset, $limit);
        if ($request->has('search')) {
            $shift_calender_employee_data = $this->searchEmployeeByNameOrId($shift_calender_employee_data, $request->search);
        }
        $total_data = count($shift_calender_employee_data);
        $shift_calender_employee_data = $shift_calender_employee_data->toArray();
        usort($shift_calender_employee_data, array($this,'employeeSortByPDisplayPriority'));
        $departments = $this->departmentRepo->getBusinessDepartmentByBusiness($business)->pluck('name', 'id')->toArray();
        return api_response($request, null, 200, ['shift_calender_employee' => $shift_calender_employee_data, 'shift_calender_header' => $shift_calender_data['header'], 'departments' => $departments, 'total' => $total_data]);
    }

    public function assignShift($business, $id, Request $request)
    {
        $this->validate($request, [
            'shift_id'                  => 'required|integer',
            'repeat'                    => 'boolean',
            'repeat_type'               => 'string',
            'repeat_range'              => 'integer',
            'days'                      => 'array',
            'end_date'                  => 'date_format:Y-m-d'
        ]);

        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        $this->setModifier($request->manager_member);
        $business_shift = $this->businessShiftRepository->find($request->shift_id);
        if (!$business_shift) return api_response($request, null, 404);

        $business_shift = $this->businessShiftRepository->find($request->shift_id);
        $shift_calender = $this->shiftAssignmentRepository->find($id);
        $this->shiftCalenderRequester->setShiftId($request->shift_id)
            ->setShiftName($business_shift->name)
            ->setStartTime($business_shift->start_time)
            ->setEndTime($business_shift->end_time)
            ->setIsHalfDayActivated($business_shift->is_halfday_enable)
            ->setIsGeneralActivated(0)
            ->setIsUnassignedActivated(0)
            ->setIsShiftActivated(1)
            ->setColorCode($business_shift->color_code);

        $request->repeat ? $this->checkRepeat($request, $shift_calender, $business_member) : $this->checkAndAssign($shift_calender);
        if ($this->shiftCalenderRequester->hasError()) return api_response($request, null, $this->shiftCalenderRequester->getErrorCode(), ['message' => $this->shiftCalenderRequester->getErrorMessage()]);
        return api_response($request, null, 200);
    }

    public function assignGeneralAttendance($business, $id, Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        $shift_calender = $this->shiftAssignmentRepository->find($id);
        $shift_calender = $this->shiftAssignmentRepository->where('business_member_id', $shift_calender->business_member_id)->where('is_shift', 1)->get();

        $this->setModifier($request->manager_member);
        $this->setIsHalfDayActivated(0)
            ->setIsGeneralActivated(1)
            ->setIsShiftActivated(0);

        foreach($shift_calender as $as_shift)
        {
            $this->shiftRemover->setShiftCalenderRequester($this->shiftCalenderRequester)->update($as_shift);
        }
        return api_response($request, null, 200);
    }

    public function dashboard(Request $request, ShiftAssignmentRepository $shift_assignment_repository)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        $total_active_employee_ids = $business->getActiveBusinessMember()->pluck('id')->toArray();
        $under_general_attendance_count = $shift_assignment_repository->where('business_member_id', $total_active_employee_ids)->where('is_general', 1)->where('date', '<', Carbon::now()->toDateString())->count();
        $under_shift_count = $shift_assignment_repository->where('business_member_id', $total_active_employee_ids)->where('is_shift', 1)->where('date', '<', Carbon::now()->toDateString())->count();
        $unassigned_shift_count = $shift_assignment_repository->where('business_member_id', $total_active_employee_ids)->where('is_unassigned', 1)->where('date', '>', Carbon::now()->toDateString())->count();

        return api_response($request, null, 200, ['dashboard' => [
            'total_employee' => count($total_active_employee_ids),
            'under_general_attendance' => $under_general_attendance_count,
            'under_shift_count' => $under_shift_count,
            'unassigned_shift_count' => $unassigned_shift_count
        ]]);
    }

    public function details($business, $id, Request $request)
    {
        /** @var Business $business */
        $business = $request->business;
        if (!$business) return api_response($request, null, 401);
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);

        $shift_calender = $this->shiftAssignmentRepository->find($id);

        if (!$shift_calender) return api_response($request, null, 404);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $member = new Item($shift_calender, new EmployeeShiftDetailsTransformer());
        $shift_calender = $manager->createData($member)->toArray()['data'];
        return api_response($request, $shift_calender, 200, ['shift_calender' => $shift_calender]);
    }

    private function employeeSortByPDisplayPriority($a, $b)
    {
        if ($a['display_priority'] < $b['display_priority']) return 0;
        return 1;
    }

    private function searchEmployeeByNameOrId($active_business_member, $search_key)
    {
        return $active_business_member->filter(function($item) use($search_key) {
            return preg_match("/{$search_key}/i", $item['employee']['name']) || preg_match("/{$search_key}/i", $item['employee']['employee_id']);
        });

    }

    private function getDatesFromDayRepeat($start_date, $end_date, $repeat)
    {
        $dates = [];
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);
        for($date = $start_date->copy(); $date->lte($end_date); $date->addDays($repeat)) {
            $dates[] = $date->format('Y-m-d');
        }
        return $dates;
    }

    private function getDatesFromWeekRepeat($start_date, $end_date, $repeat, $days)
    {
        $end_date = Carbon::parse($end_date);
        foreach ($days as $day)
        {
            $day = date('N', strtotime($day));
            $start_date = Carbon::parse($start_date)->next($day - 1);
            for($date = $start_date->copy(); $date->lte($end_date); $date->addWeeks($repeat)) {
                $dates[] = $date->format('Y-m-d');
            }
        }
        return $dates;
    }

    private function checkEndTime()
    {
        $endTime = Carbon::parse($this->shiftCalenderRequester->getEndTime());
        $check_time = Carbon::parse("22:00:00");
        return $endTime->gte($check_time);
    }

    private function assignShiftCalender($shift_calender)
    {
        return $this->shiftCalenderCreator->setShiftCalenderRequester($this->shiftCalenderRequester)->update($shift_calender);
    }

    private function checkRepeat($request, $shift_calender, $business_member)
    {
        $dates = [];
        if($request->repeat_type == 'days')
            $dates = $this->getDatesFromDayRepeat($shift_calender->date, $request->end_date, $request->repeat_range);
        elseif ($request->repeat_type == 'weeks')
            $dates = $this->getDatesFromWeekRepeat($shift_calender->date, $request->end_date, $request->repeat_range, $request->days);

        foreach ($dates as $date)
        {
            $shift_calender = $this->shiftAssignmentRepository->where('business_member_id', $business_member->id)->where('date', $date)->first();
            $this->checkAndAssign($shift_calender);
        }
    }

    private function checkAndAssign($shift_calender)
    {
        $this->checkEndTime() ? $this->checkNextDayShift($shift_calender) : $this->assignShiftCalender($shift_calender);
    }

    private function checkNextDayShift($shift_calender)
    {
        $next_date = Carbon::parse($shift_calender->date)->addDay()->toDateString();
        $check_next_date_shift = $this->shiftAssignmentRepository->where('business_member_id', $shift_calender->business_member_id)->where('date', $next_date)->first();

        if($check_next_date_shift->shift_name) return $this->checkShiftTimeGap($check_next_date_shift, $shift_calender);
        return $this->assignShiftCalender($shift_calender);
    }

    private function checkShiftTimeGap($check_next_date_shift, $shift_calender)
    {
        $endTime = Carbon::parse($this->shiftCalenderRequester->getEndTime());
        $next_day_start_time = Carbon::parse($check_next_date_shift->start_time)->addDay();
        return $next_day_start_time->diffInHours($endTime) >= 2 ? $this->assignShiftCalender($shift_calender) : $this->shiftCalenderRequester->setShiftAssignError();
    }
}
