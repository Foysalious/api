<?php namespace Sheba\Business\ShiftSetting\ShiftAssign;

use Carbon\Carbon;
use Sheba\Dal\BusinessShift\BusinessShiftRepository;
use Sheba\Dal\ShiftCalender\ShiftCalenderRepository;
use Sheba\Helpers\HasErrorCodeAndMessage;

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
    /*** @var BusinessShiftRepository  */
    private $businessShiftRepository;
    private $colorCode;
    /**
     * @var \Illuminate\Foundation\Application|mixed
     */
    private $shiftCalenderRepository;

    public function __construct()
    {
        $this->shiftCalenderRepository = app(ShiftCalenderRepository::class);
    }

    public function setShiftId($shiftId)
    {
        $this->shiftId = $shiftId;
//        $this->checkExistingShift();
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

    public function setColorCode($color_code)
    {
        $this->colorCode = $color_code;
        return $this;
    }

    public function getColorCode()
    {
        return $this->colorCode;
    }

    private function checkExistingShift()
    {
//        $this->shiftCalenderRepository->where('')
//        $this->setError(400);
    }

    private function checkTimeGap()
    {
        return $this->getEndTime();
    }
}