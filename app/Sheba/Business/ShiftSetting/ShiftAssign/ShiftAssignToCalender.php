<?php namespace Sheba\Business\ShiftSetting\ShiftAssign;

use Carbon\Carbon;
use Sheba\Dal\ShiftAssignment\ShiftAssignmentRepository;

class ShiftAssignToCalender
{
    /** @var ShiftAssignmentRepository $shiftCalenderRequester */
    private $shiftAssignmentRepository;
    /** @var Requester $shiftCalenderRequester */
    private $shiftCalenderRequester;
    private $errorCount = 0;

    public function __construct(ShiftAssignmentRepository $shift_assignment_repository)
    {
        $this->shiftAssignmentRepository = $shift_assignment_repository;
    }

    public function checkShiftRepeat($request, $shift_calender, $business_member, Requester $shift_calender_requester)
    {
        $this->shiftCalenderRequester = $shift_calender_requester;
        $request->repeat ? $this->checkRepeat($request, $shift_calender, $business_member) : $this->checkAndAssign($shift_calender);
    }

    public function checkRepeat($request, $shift_calender, $business_member)
    {
        $dates = [];
        if($request->repeat_type == 'days') $dates = $this->getDatesFromDayRepeat($shift_calender->date, $request->end_date, $request->repeat_range);
        elseif ($request->repeat_type == 'weeks') $dates = $this->getDatesFromWeekRepeat($shift_calender->date, $request->end_date, $request->repeat_range, $request->days);
dd($dates);
        foreach ($dates as $date)
        {
            $shift_calender = $this->shiftAssignmentRepository->where('business_member_id', $business_member->id)->where('date', $date)->first();
            $this->checkAndAssign($shift_calender);
        }
        if($this->errorCount > 0)
        {
            $message = "Could not assign shift because it overlaps with ". $this->errorCount ." shifts.";
            $this->shiftCalenderRequester->setShiftAssignError($message);
        }
    }

    public function checkAndAssign($shift_calender)
    {
        $this->checkEndTime() ? $this->checkNextDayShift($shift_calender) : $this->shiftCalenderRequester->setData($shift_calender);
    }

    public function checkEndTime()
    {
        $endTime = Carbon::parse($this->shiftCalenderRequester->getEndTime());
        $check_time = Carbon::parse("22:00:00");
        return $endTime->gte($check_time);
    }

    public function checkNextDayShift($shift_calender)
    {
        $next_date = Carbon::parse($shift_calender->date)->addDay()->toDateString();
        $check_next_date_shift = $this->shiftAssignmentRepository->where('business_member_id', $shift_calender->business_member_id)->where('date', $next_date)->first();

        if($check_next_date_shift->shift_name) return $this->checkShiftTimeGap($check_next_date_shift, $shift_calender);
        return $this->shiftCalenderRequester->setData($shift_calender);
    }

    public function checkShiftTimeGap($check_next_date_shift, $shift_calender)
    {
        $endTime = Carbon::parse($this->shiftCalenderRequester->getEndTime());
        $next_day_start_time = Carbon::parse($check_next_date_shift->start_time)->addDay();
        return $next_day_start_time->diffInHours($endTime) >= 2 ? $this->shiftCalenderRequester->setData($shift_calender) : $this->errorCount++;
    }

    public function getDatesFromDayRepeat($start_date, $end_date, $repeat)
    {
        $dates = [];
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);
        for($date = $start_date->copy(); $date->lte($end_date); $date->addDays($repeat)) {
            $dates[] = $date->format('Y-m-d');
        }
        return $dates;
    }

    public function getDatesFromWeekRepeat($start_date, $end_date, $repeat, $days)
    {
        $dates = [];
        $end_date = Carbon::parse($end_date)->toDateString();
        $date = $start_date;
        while($date != $end_date)
        {
            foreach ($days as $d)
            {
                if ($d == "Saturday" && Carbon::parse($date)->isSaturday())  $dates[] = $date;
                if ($d == "Sunday" && Carbon::parse($date)->isSunday())  $dates[] = $date;
                if ($d == "Monday" && Carbon::parse($date)->isMonday())  $dates[] = $date;
                if ($d == "Tuesday" && Carbon::parse($date)->isTuesday())  $dates[] = $date;
                if ($d == "Wednesday" && Carbon::parse($date)->isWednesday())  $dates[] = $date;
                if ($d == "Thursday" && Carbon::parse($date)->isThursday())  $dates[] = $date;
                if ($d == "Friday" && Carbon::parse($date)->isFriday())  $dates[] = $date;
            }
            $date = Carbon::parse($date)->addDay()->toDateString();
        }

//        dd($dates);
//
//
//
//
//
//
//
//        foreach ($days as $day) {
//            $day = date('N', strtotime($day));
//            $start_date = Carbon::parse($start_date)->next($day);
////            dd($start_date->copy());
//            for ($date = $start_date->copy(); $date->lte($end_date); $date->addWeeks($repeat)) {
//                $dates[] = $date->format('Y-m-d');
//            }
//        }
//        dd($dates);
        return $dates;
    }
}
