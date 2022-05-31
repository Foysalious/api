<?php namespace Sheba\Business\ShiftSetting\ShiftAssign;

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
    private $calender_data = [];

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
}
