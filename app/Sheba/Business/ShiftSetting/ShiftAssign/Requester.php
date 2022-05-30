<?php namespace Sheba\Business\ShiftSetting\ShiftAssign;

use Carbon\Carbon;
use Sheba\Helpers\HasErrorCodeAndMessage;
use Sheba\Dal\ShiftAssignment\ShiftAssignmentRepository;

class Requester
{
    use HasErrorCodeAndMessage;

    private $shiftId;
    private $name;
    private $startTime;
    private $endTime;
    private $isHalfDayActivated;
    private $isGeneralActivated;
    private $isUnassignedActivated;
    private $isShiftActivated;
    private $colorCode;
    private $repeat;
    private $repeat_type;
    private $repeat_range;
    private $days;
    private $end_date;
    private $errorCount = 0;
    private $calender_data = [];

    /*** @var ShiftAssignmentRepository */
    private $shiftAssignmentRepository;
    /*** @var Creator */
    private $shiftCalenderCreator;

    public function __construct(ShiftAssignmentRepository $shiftAssignmentRepository, Creator $shiftCalenderCreator)
    {
        $this->shiftAssignmentRepository = $shiftAssignmentRepository;
        $this->shiftCalenderCreator = $shiftCalenderCreator;
    }

    public function setShiftId($shiftId)
    {
        $this->shiftId = $shiftId;
        return $this;
    }

    public function getShiftId()
    {
        return $this->shiftId;
    }

    public function setShiftName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getShiftName()
    {
        return $this->name;
    }

    public function setStartTime($start_time)
    {
        $this->startTime = $start_time;
        return $this;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function setEndTime($end_time)
    {
        $this->endTime = $end_time;
        return $this;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }

    public function setIsHalfDayActivated($is_half_day_activated)
    {
        $this->isHalfDayActivated = $is_half_day_activated;
        return $this;
    }

    public function getIsHalfDayActivated()
    {
        return $this->isHalfDayActivated;
    }

    public function setIsGeneralActivated($is_general_activated)
    {
        $this->isGeneralActivated = $is_general_activated;
        return $this;
    }

    public function getIsGeneralActivated()
    {
        return $this->isGeneralActivated;
    }

    public function setIsUnassignedActivated($is_unassigned_activated)
    {
        $this->isUnassignedActivated = $is_unassigned_activated;
        return $this;
    }

    public function getIsUnassignedActivated()
    {
        return $this->isUnassignedActivated;
    }

    public function setIsShiftActivated($is_shift_activated)
    {
        $this->isShiftActivated = $is_shift_activated;
        return $this;
    }

    public function getIsShiftActivated()
    {
        return $this->isShiftActivated;
    }

    public function setRepeat($repeat)
    {
        $this->repeat = $repeat;
        return $this;
    }

    public function getRepeat()
    {
        return $this->repeat;
    }

    public function setRepeatType($repeat_type)
    {
        $this->repeat_type = $repeat_type;
        return $this;
    }

    public function getRepeatType()
    {
        return $this->repeat_type;
    }

    public function setRepeatRange($repeat_range)
    {
        $this->repeat_range = $repeat_range;
        return $this;
    }

    public function getRepeatRange()
    {
        return $this->repeat_range;
    }

    public function setRepeatDays($days)
    {
        $this->days = $days;
        return $this;
    }

    public function getRepeatDays()
    {
        return $this->days;
    }

    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
        return $this;
    }

    public function getEndDate()
    {
        return $this->end_date;
    }

    public function setColorCode($color_code)
    {
        $this->colorCode = $color_code;
        return $this;
    }

    public function getColorCode()
    {
        return $this->colorCode;
    }

    public function setData($calender_data)
    {
        $this->calender_data[] = $calender_data;
        return $this;
    }

    public function getData()
    {
        return $this->calender_data;
    }

    public function setShiftAssignError($message)
    {
        $this->setError(400, $message);
    }

    public function checkRepeat($request, $shift_calender, $business_member)
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
        if($this->errorCount > 0)
        {
            $message = "Could not assign shift because it overlaps with ". $this->errorCount ." shifts.";
            $this->setShiftAssignError($message);
        }
    }

    public function checkAndAssign($shift_calender)
    {
        $this->checkEndTime() ? $this->checkNextDayShift($shift_calender) : $this->setData($shift_calender);
    }

    public function checkEndTime()
    {
        $endTime = Carbon::parse($this->getEndTime());
        $check_time = Carbon::parse("22:00:00");
        return $endTime->gte($check_time);
    }

    public function checkNextDayShift($shift_calender)
    {
        $next_date = Carbon::parse($shift_calender->date)->addDay()->toDateString();
        $check_next_date_shift = $this->shiftAssignmentRepository->where('business_member_id', $shift_calender->business_member_id)->where('date', $next_date)->first();

        if($check_next_date_shift->shift_name) return $this->checkShiftTimeGap($check_next_date_shift, $shift_calender);
        return $this->setData($shift_calender);
    }

    public function checkShiftTimeGap($check_next_date_shift, $shift_calender)
    {
        $endTime = Carbon::parse($this->getEndTime());
        $next_day_start_time = Carbon::parse($check_next_date_shift->start_time)->addDay();
        return $next_day_start_time->diffInHours($endTime) >= 2 ? $this->setData($shift_calender) : $this->errorCount++;
    }

    public function getShiftSettings()
    {
        $shift_settings = [
            'repeat'        => $this->getRepeat(),
            'repeat_type'   => $this->getRepeatType(),
            'repeat_range'  => $this->getRepeatRange(),
            'days'          => $this->getRepeatDays(),
            'end_date'      => $this->getEndDate()
        ];
        return json_encode($shift_settings);
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
        $end_date = Carbon::parse($end_date);
        foreach ($days as $day) {
            $day = date('N', strtotime($day));
            $start_date = Carbon::parse($start_date)->next($day - 1);
            for ($date = $start_date->copy(); $date->lte($end_date); $date->addWeeks($repeat)) {
                $dates[] = $date->format('Y-m-d');
            }
        }
        return $dates;
    }
}
