<?php namespace Sheba\Business\ShiftSetting\ShiftAssign;

use Sheba\Dal\ShiftCalender\ShiftCalenderRepository;

class Creator
{
    /*** @var ShiftCalenderRepository  */
    private $shiftCalenderRepository;

    public function __construct()
    {
        $this->shiftCalenderRepository = app(ShiftCalenderRepository::class);
    }

    /** @var Requester $shiftCalenderRequester */
    private $shiftCalenderRequester;

    public function setShiftCalenderRequester(Requester $shiftCalenderRequester)
    {
        $this->shiftCalenderRequester = $shiftCalenderRequester;
        return $this;
    }

    public function update($shift_calender)
    {
        $data = $this->makeData();
        $this->shiftCalenderRepository->update($shift_calender, $data);
    }

    public function assignGeneral($calender)
    {
        $data = $this->makeData();
        $this->shiftCalenderRepository->update($calender, $data);
    }

    private function makeData()
    {
        return [
            'shift_id' => $this->shiftCalenderRequester->getShiftId(),
            'shift_name' => $this->shiftCalenderRequester->getShiftName(),
            'start_time' => $this->shiftCalenderRequester->getStartTime(),
            'end_time' => $this->shiftCalenderRequester->getEndTime(),
            'is_half_day' => $this->shiftCalenderRequester->getIsHalfDayActivated(),
            'is_general' => $this->shiftCalenderRequester->getIsGeneralActivated(),
            'is_unassigned' => $this->shiftCalenderRequester->getIsUnassignedActivated(),
            'is_shift' => $this->shiftCalenderRequester->getIsShiftActivated(),
            'color_code' => $this->shiftCalenderRequester->getColorCode()
        ];
    }
}